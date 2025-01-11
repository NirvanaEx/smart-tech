<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

// Регистрация нового пользователя
function registerUser($data)
{
    $pdo = getDatabaseConnection();

    // Проверка обязательных полей
    if (empty($data['login']) || empty($data['password']) || empty($data['password_repeat'])) {
        Response::send(400, "Missing required fields: login, password, password_repeat");
    }

    if ($data['password'] !== $data['password_repeat']) {
        Response::send(400, "Passwords do not match");
    }

    try {
        // Проверяем уникальность login
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = :login");
        $stmt->execute(['login' => $data['login']]);
        if ($stmt->rowCount() > 0) {
            Response::send(400, "Login already exists");
        }

        // Создаём запись в таблице users
        $stmt = $pdo->prepare("INSERT INTO users (login, password, role, date_creation, updated_at) VALUES (:login, :password, 'user', NOW(), NOW())");
        $stmt->execute([
            'login' => $data['login'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        Response::send(201, "User registered successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to register user: " . $e->getMessage());
    }
}

// Авторизация пользователя
function loginUser($data)
{
    $pdo = getDatabaseConnection();

    // Проверка обязательных полей
    if (empty($data['login']) || empty($data['password'])) {
        Response::send(400, "Missing required fields: login, password");
    }

    try {
        // Проверяем наличие пользователя
        $stmt = $pdo->prepare("SELECT id, login, password, role FROM users WHERE login = :login");
        $stmt->execute(['login' => $data['login']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::send(401, "Invalid login or password");
        }

        // Успешная авторизация
        Response::send(200, "Login successful", [
            'user_id' => $user['id'],
            'login' => $user['login'],
            'role' => $user['role'],
        ]);
    } catch (PDOException $e) {
        Response::send(500, "Failed to login: " . $e->getMessage());
    }
}

// Получение данных пользователя
function getUserData($data)
{
    $pdo = getDatabaseConnection();

    // Проверка обязательного поля user_id
    if (empty($data['user_id'])) {
        Response::send(400, "Missing required field: user_id");
    }

    try {
        // Обновляем запрос для использования таблицы user_full_data
        $stmt = $pdo->prepare("
            SELECT 
                u.id AS user_id, 
                u.login, 
                u.role, 
                ufd.surname, 
                ufd.name, 
                ufd.email, 
                ufd.address, 
                ufd.phone, 
                ufd.date_creation, 
                ufd.updated_at
            FROM users u
            LEFT JOIN user_full_data ufd ON u.id = ufd.user_id
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $data['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Response::send(404, "User not found");
        }

        Response::send(200, "User data retrieved successfully", $user);
    } catch (PDOException $e) {
        Response::send(500, "Failed to retrieve user data: " . $e->getMessage());
    }
}

// Обновление данных пользователя
function updateUserData($data)
{
    $pdo = getDatabaseConnection();

    // Проверка обязательного поля user_id
    if (empty($data['user_id'])) {
        Response::send(400, "Missing required field: user_id");
    }

    try {
        // Проверяем, существует ли запись в user_full_data
        $stmtCheck = $pdo->prepare("SELECT 1 FROM user_full_data WHERE user_id = :user_id");
        $stmtCheck->execute(['user_id' => $data['user_id']]);

        if ($stmtCheck->rowCount() === 0) {
            // Если записи нет, создаём новую
            $stmtInsert = $pdo->prepare("
                INSERT INTO user_full_data (user_id, surname, name, email, address, phone, date_creation, updated_at) 
                VALUES (:user_id, :surname, :name, :email, :address, :phone, NOW(), NOW())
            ");
            $stmtInsert->execute([
                'user_id' => $data['user_id'],
                'surname' => $data['surname'] ?? null,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);
            Response::send(201, "User data created successfully");
        } else {
            // Обновляем существующую запись
            $stmtUpdate = $pdo->prepare("
                UPDATE user_full_data 
                SET surname = :surname, 
                    name = :name, 
                    email = :email, 
                    address = :address, 
                    phone = :phone, 
                    updated_at = NOW() 
                WHERE user_id = :user_id
            ");
            $stmtUpdate->execute([
                'user_id' => $data['user_id'],
                'surname' => $data['surname'] ?? null,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if ($stmtUpdate->rowCount() === 0) {
                Response::send(404, "No changes made to the user data");
            }

            Response::send(200, "User data updated successfully");
        }
    } catch (PDOException $e) {
        Response::send(500, "Failed to update user data: " . $e->getMessage());
    }
}


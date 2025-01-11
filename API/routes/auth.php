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
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE login = :login");
        $stmt->execute(['login' => $data['login']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::send(401, "Invalid login or password");
        }

        // Успешная авторизация
        Response::send(200, "Login successful", [
            'user_id' => $user['id'],
            'role' => $user['role'],
        ]);
    } catch (PDOException $e) {
        Response::send(500, "Failed to login: " . $e->getMessage());
    }
}

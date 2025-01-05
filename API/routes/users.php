<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

// Получение списка пользователей
function getUsers() {
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->query("SELECT id, login, role, date_creation FROM users");
        $users = $stmt->fetchAll();
        Response::send(200, "Users fetched successfully", $users);
    } catch (PDOException $e) {
        Response::send(500, "Failed to fetch users: " . $e->getMessage());
    }
}

// Добавление нового пользователя
function addUser($data) {
    $pdo = getDatabaseConnection();

    // Проверка обязательных полей
    if (empty($data['login']) || empty($data['password']) || empty($data['role'])) {
        Response::send(400, "Missing required fields: login, password, role");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (login, password, role, date_creation) VALUES (:login, :password, :role, NOW())");
        $stmt->execute([
            'login' => $data['login'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role']
        ]);
        Response::send(201, "User added successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to add user: " . $e->getMessage());
    }
}

// Изменение данных пользователя
function updateUser($id, $data) {
    $pdo = getDatabaseConnection();

    // Проверка существования пользователя
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "User not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying user: " . $e->getMessage());
    }

    // Построение запроса на обновление
    $fields = [];
    $params = ['id' => $id];
    if (!empty($data['login'])) {
        $fields[] = "login = :login";
        $params['login'] = $data['login'];
    }
    if (!empty($data['password'])) {
        $fields[] = "password = :password";
        $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    if (!empty($data['role'])) {
        $fields[] = "role = :role";
        $params['role'] = $data['role'];
    }

    if (empty($fields)) {
        Response::send(400, "No fields provided for update");
    }

    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        Response::send(200, "User updated successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to update user: " . $e->getMessage());
    }
}

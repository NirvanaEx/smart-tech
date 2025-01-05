<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

// Получение списка категорий
function getCategories() {
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->query("SELECT id, name, date_creation FROM category");
        $categories = $stmt->fetchAll();
        Response::send(200, "Categories fetched successfully", $categories);
    } catch (PDOException $e) {
        Response::send(500, "Failed to fetch categories: " . $e->getMessage());
    }
}

// Добавление новой категории
function addCategory($data) {
    $pdo = getDatabaseConnection();

    // Проверка обязательных полей
    if (empty($data['name'])) {
        Response::send(400, "Missing required field: name");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO category (name, date_creation) VALUES (:name, NOW())");
        $stmt->execute([
            'name' => $data['name']
        ]);
        Response::send(201, "Category added successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to add category: " . $e->getMessage());
    }
}

// Изменение данных категории
function updateCategory($id, $data) {
    $pdo = getDatabaseConnection();

    // Проверка существования категории
    try {
        $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Category not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying category: " . $e->getMessage());
    }

    // Построение запроса на обновление
    $fields = [];
    $params = ['id' => $id];
    if (!empty($data['name'])) {
        $fields[] = "name = :name";
        $params['name'] = $data['name'];
    }

    if (empty($fields)) {
        Response::send(400, "No fields provided for update");
    }

    $query = "UPDATE category SET " . implode(", ", $fields) . " WHERE id = :id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        Response::send(200, "Category updated successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to update category: " . $e->getMessage());
    }
}

// Удаление категории
function deleteCategory($id) {
    $pdo = getDatabaseConnection();

    // Проверка существования категории
    try {
        $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Category not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying category: " . $e->getMessage());
    }

    // Удаление категории
    try {
        $stmt = $pdo->prepare("DELETE FROM category WHERE id = :id");
        $stmt->execute(['id' => $id]);
        Response::send(200, "Category deleted successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to delete category: " . $e->getMessage());
    }
}

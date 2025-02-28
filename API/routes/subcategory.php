<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

// Получение списка подкатегорий
// Получение списка подкатегорий
function getSubcategories() {
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->query("SELECT id, category_id, name, date_creation, updated_at FROM subcategory");
        $subcategories = $stmt->fetchAll();
        Response::send(200, "Subcategories fetched successfully", $subcategories);
    } catch (PDOException $e) {
        Response::send(500, "Failed to fetch subcategories: " . $e->getMessage());
    }
}

// Добавление новой подкатегории
function addSubcategory($data) {
    $pdo = getDatabaseConnection();

    // Проверка обязательных полей
    if (empty($data['name']) || empty($data['category_id'])) {
        Response::send(400, "Missing required fields: name, category_id");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO subcategory (category_id, name, date_creation, updated_at) 
            VALUES (:category_id, :name, NOW(), NOW())
        ");
        $stmt->execute([
            'category_id' => $data['category_id'],
            'name' => $data['name']
        ]);
        Response::send(201, "Subcategory added successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to add subcategory: " . $e->getMessage());
    }
}

// Обновление подкатегории
function updateSubcategory($id, $data) {
    $pdo = getDatabaseConnection();

    // Проверка существования подкатегории
    try {
        $stmt = $pdo->prepare("SELECT id FROM subcategory WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Subcategory not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying subcategory: " . $e->getMessage());
    }

    // Построение запроса на обновление
    $fields = [];
    $params = ['id' => $id];
    if (!empty($data['name'])) {
        $fields[] = "name = :name";
        $params['name'] = $data['name'];
    }
    if (!empty($data['category_id'])) {
        $fields[] = "category_id = :category_id";
        $params['category_id'] = $data['category_id'];
    }
    $fields[] = "updated_at = NOW()"; // Обновляем timestamp

    if (empty($fields)) {
        Response::send(400, "No fields provided for update");
    }

    $query = "UPDATE subcategory SET " . implode(", ", $fields) . " WHERE id = :id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        Response::send(200, "Subcategory updated successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to update subcategory: " . $e->getMessage());
    }
}

// Удаление подкатегории
function deleteSubcategory($id) {
    $pdo = getDatabaseConnection();

    // Проверка существования подкатегории
    try {
        $stmt = $pdo->prepare("SELECT id FROM subcategory WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Subcategory not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying subcategory: " . $e->getMessage());
    }

    // Удаление подкатегории
    try {
        $stmt = $pdo->prepare("DELETE FROM subcategory WHERE id = :id");
        $stmt->execute(['id' => $id]);
        Response::send(200, "Subcategory deleted successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to delete subcategory: " . $e->getMessage());
    }
}

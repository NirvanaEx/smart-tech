<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

// Получение списка товаров с пагинацией
function getProducts($start, $limit) {
    $pdo = getDatabaseConnection();

    // Проверка значений
    if (!is_numeric($start) || !is_numeric($limit)) {
        Response::send(400, "Start and limit must be numeric values");
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                products.id, 
                products.name AS product_name, 
                products.description, 
                products.quantity, 
                products.date_creation, 
                products.updated_at, 
                products.data_status,
                subcategory.name AS subcategory_name
            FROM products
            INNER JOIN subcategory ON products.subcategory_id = subcategory.id
            ORDER BY products.id
            LIMIT :start, :limit
        ");
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        $products = $stmt->fetchAll();
        Response::send(200, "Products fetched successfully", $products);
    } catch (PDOException $e) {
        Response::send(500, "Failed to fetch products: " . $e->getMessage());
    }
}

// Создание нового товара
function addProduct($data) {
    $pdo = getDatabaseConnection();

    // Проверка обязательных полей
    if (empty($data['name']) || empty($data['subcategory_id']) || empty($data['description']) || !isset($data['quantity'])) {
        Response::send(400, "Missing required fields: name, subcategory_id, description, quantity");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (subcategory_id, name, description, quantity, data_status, date_creation, updated_at) 
            VALUES (:subcategory_id, :name, :description, :quantity, 'available', NOW(), NOW())
        ");
        $stmt->execute([
            'subcategory_id' => $data['subcategory_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'quantity' => $data['quantity']
        ]);
        Response::send(201, "Product added successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to add product: " . $e->getMessage());
    }
}

// Поиск товаров
function searchProducts($query) {
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("
            SELECT 
                products.id, 
                products.name AS product_name, 
                products.description, 
                products.quantity, 
                products.date_creation, 
                products.updated_at, 
                products.data_status,
                subcategory.name AS subcategory_name
            FROM products
            INNER JOIN subcategory ON products.subcategory_id = subcategory.id
            WHERE products.name LIKE :query OR products.description LIKE :query
            ORDER BY products.id
        ");
        $stmt->execute(['query' => '%' . $query . '%']);

        $products = $stmt->fetchAll();
        Response::send(200, "Search results fetched successfully", $products);
    } catch (PDOException $e) {
        Response::send(500, "Failed to search products: " . $e->getMessage());
    }
}

// Удаление товара
function deleteProduct($id) {
    $pdo = getDatabaseConnection();

    // Проверка существования товара
    try {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Product not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying product: " . $e->getMessage());
    }

    // Удаление товара
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        Response::send(200, "Product deleted successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to delete product: " . $e->getMessage());
    }
}

// Обновление товара
function updateProduct($id, $data) {
    $pdo = getDatabaseConnection();

    // Проверка существования товара
    try {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Product not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error verifying product: " . $e->getMessage());
    }

    // Построение запроса на обновление
    $fields = [];
    $params = ['id' => $id];
    if (!empty($data['name'])) {
        $fields[] = "name = :name";
        $params['name'] = $data['name'];
    }
    if (!empty($data['description'])) {
        $fields[] = "description = :description";
        $params['description'] = $data['description'];
    }
    if (isset($data['quantity'])) {
        $fields[] = "quantity = :quantity";
        $params['quantity'] = $data['quantity'];
    }
    if (!empty($data['data_status'])) {
        $fields[] = "data_status = :data_status";
        $params['data_status'] = $data['data_status'];
    }
    $fields[] = "updated_at = NOW()";

    if (empty($fields)) {
        Response::send(400, "No fields provided for update");
    }

    $query = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = :id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        Response::send(200, "Product updated successfully");
    } catch (PDOException $e) {
        Response::send(500, "Failed to update product: " . $e->getMessage());
    }
}

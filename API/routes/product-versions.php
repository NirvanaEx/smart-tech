<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Получение версии(й) товара.
 * Если передан параметр product_id – возвращаются все версии для данного товара.
 * Если передан параметр id – возвращается конкретная версия.
 * Если параметры не переданы – возвращаются все версии.
 */
function getProductVersions() {
    try {
        $pdo = getDatabaseConnection();

        if (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            $stmt = $pdo->prepare("SELECT * FROM product_versions WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($versions && count($versions) > 0) {
                Response::send(200, "Product versions retrieved successfully", $versions);
            } else {
                Response::send(404, "Product versions not found");
            }
        } elseif (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM product_versions WHERE id = ?");
            $stmt->execute([$id]);
            $version = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($version) {
                Response::send(200, "Product version retrieved successfully", $version);
            } else {
                Response::send(404, "Product version not found");
            }
        } else {
            // Возвращаем все версии, если не переданы параметры
            $stmt = $pdo->query("SELECT * FROM product_versions");
            $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::send(200, "Product versions retrieved successfully", $versions);
        }
    } catch (Exception $e) {
        Response::send(500, "An error occurred while retrieving product versions", ["error" => $e->getMessage()]);
    }
}

/**
 * Создание новой версии товара.
 * Ожидаемые поля (в JSON): product_id, name, description
 */
function addProductVersion() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['product_id']) || !isset($data['name']) || !isset($data['description'])) {
        Response::send(400, "Missing required fields: product_id, name, description");
    }
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("
            INSERT INTO product_versions (product_id, name, description, date_creation, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$data['product_id'], $data['name'], $data['description']]);
        Response::send(201, "Product version added successfully");
    } catch (Exception $e) {
        Response::send(500, "An error occurred while adding product version", ["error" => $e->getMessage()]);
    }
}

/**
 * Обновление версии товара.
 * Параметр $id – идентификатор версии, обновляются переданные поля (например, name, description)
 */
function updateProductVersion($id) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data)) {
        Response::send(400, "No data provided for update");
    }
    try {
        $pdo = getDatabaseConnection();
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params['description'] = $data['description'];
        }
        if (empty($fields)) {
            Response::send(400, "No valid fields provided for update");
        }
        $params['id'] = $id;
        $query = "UPDATE product_versions SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        Response::send(200, "Product version updated successfully");
    } catch (Exception $e) {
        Response::send(500, "An error occurred while updating product version", ["error" => $e->getMessage()]);
    }
}

/**
 * Удаление версии товара по идентификатору.
 */
function deleteProductVersion($id) {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("DELETE FROM product_versions WHERE id = ?");
        $stmt->execute([$id]);
        Response::send(200, "Product version deleted successfully");
    } catch (Exception $e) {
        Response::send(500, "An error occurred while deleting product version", ["error" => $e->getMessage()]);
    }
}

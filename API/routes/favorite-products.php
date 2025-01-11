<?php
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../config/db.php';

// Получение списка избранных продуктов пользователя
function getFavoriteProducts($user_id)
{
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("SELECT fp.id, fp.product_id, p.name AS product_name, p.description, p.image_path, pp.price
                               FROM favorite_products fp
                               JOIN products p ON fp.product_id = p.id
                               LEFT JOIN product_price pp ON p.id = pp.product_id
                               WHERE fp.user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::send(200, "Favorite products fetched successfully", $favorites);
    } catch (PDOException $e) {
        Response::send(500, "Error fetching favorite products: " . $e->getMessage());
    }
}

// Добавление продукта в избранное
function addFavoriteProduct($data)
{
    $pdo = getDatabaseConnection();

    $user_id = $data['user_id'] ?? null;
    $product_id = $data['product_id'] ?? null;

    if (!$user_id || !$product_id) {
        Response::send(400, "User ID and Product ID are required");
        return;
    }

    try {
        // Проверяем, есть ли уже этот продукт в избранном
        $stmt = $pdo->prepare("SELECT id FROM favorite_products WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            Response::send(200, "Product is already in favorites");
        } else {
            // Добавляем новый продукт в избранное
            $insertStmt = $pdo->prepare("INSERT INTO favorite_products (user_id, product_id, date_creation, updated_at) VALUES (:user_id, :product_id, NOW(), NOW())");
            $insertStmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
            Response::send(201, "Product added to favorites successfully");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error adding product to favorites: " . $e->getMessage());
    }
}

// Удаление продукта из избранного
function deleteFavoriteProduct($id)
{
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("DELETE FROM favorite_products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        Response::send(200, "Favorite product deleted successfully");
    } catch (PDOException $e) {
        Response::send(500, "Error deleting favorite product: " . $e->getMessage());
    }
}

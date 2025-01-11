<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';


// Получение корзины пользователя
function getCart($user_id)
{
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("SELECT c.id, c.product_id, c.quantity, p.name AS product_name, p.description, p.image_path, pp.price
                               FROM cart c
                               JOIN products p ON c.product_id = p.id
                               LEFT JOIN product_price pp ON p.id = pp.product_id
                               WHERE c.user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::send(200, "Cart fetched successfully", $cartItems);
    } catch (PDOException $e) {
        Response::send(500, "Error fetching cart: " . $e->getMessage());
    }
}

// Добавление продукта в корзину
function addToCart($data)
{
    $pdo = getDatabaseConnection();

    $user_id = $data['user_id'] ?? null;
    $product_id = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? 1;

    if (!$user_id || !$product_id) {
        Response::send(400, "User ID and Product ID are required");
        return;
    }

    try {
        // Проверяем, есть ли уже этот продукт в корзине
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // Обновляем количество
            $newQuantity = $existingItem['quantity'] + $quantity;
            $updateStmt = $pdo->prepare("UPDATE cart SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
            $updateStmt->execute([':quantity' => $newQuantity, ':id' => $existingItem['id']]);
            Response::send(200, "Cart item updated successfully");
        } else {
            // Добавляем новый продукт в корзину
            $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, date_creation, updated_at) VALUES (:user_id, :product_id, :quantity, NOW(), NOW())");
            $insertStmt->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);
            Response::send(201, "Product added to cart successfully");
        }
    } catch (PDOException $e) {
        Response::send(500, "Error adding product to cart: " . $e->getMessage());
    }
}

// Обновление количества продукта в корзине
function updateCartItem($id, $data)
{
    $pdo = getDatabaseConnection();

    $quantity = $data['quantity'] ?? null;

    if (!$quantity || $quantity <= 0) {
        Response::send(400, "Quantity must be greater than 0");
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':quantity' => $quantity, ':id' => $id]);
        Response::send(200, "Cart item updated successfully");
    } catch (PDOException $e) {
        Response::send(500, "Error updating cart item: " . $e->getMessage());
    }
}

// Удаление продукта из корзины
function deleteCartItem($id)
{
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :id");
        $stmt->execute([':id' => $id]);
        Response::send(200, "Cart item deleted successfully");
    } catch (PDOException $e) {
        Response::send(500, "Error deleting cart item: " . $e->getMessage());
    }
}

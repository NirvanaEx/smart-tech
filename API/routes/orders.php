<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../config/db.php';

function getOrders() {
    try {
        $pdo = getDatabaseConnection();

        $query = "
            SELECT 
                o.id, 
                CONCAT(COALESCE(ufd.surname, ''), ' ', COALESCE(ufd.name, '')) AS user_name,
                COALESCE(p.name, 'Unknown Product') AS product_name,
                COALESCE(pv.name, 'Unknown Version') AS product_version,
                o.quantity, 
                o.total_price, 
                o.status, 
                o.payment_status, 
                o.date_creation
            FROM orders o
            LEFT JOIN product_versions pv ON o.product_version_id = pv.id
            LEFT JOIN products p ON pv.product_id = p.id
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN user_full_data ufd ON u.id = ufd.user_id
            ORDER BY o.date_creation DESC
        ";

        $stmt = $pdo->query($query);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($orders, "Orders retrieved successfully");
    } catch (Exception $e) {
        Response::error(500, "An error occurred while retrieving orders", ["error" => $e->getMessage()]);
    }
}

function getOrderById($id) {
    try {
        $pdo = getDatabaseConnection();

        $query = "
            SELECT 
                o.id, 
                CONCAT(COALESCE(ufd.surname, ''), ' ', COALESCE(ufd.name, '')) AS user_name,
                COALESCE(p.name, 'Unknown Product') AS product_name,
                COALESCE(pv.name, 'Unknown Version') AS product_version,
                o.quantity, 
                o.total_price, 
                o.status, 
                o.payment_status, 
                o.date_creation
            FROM orders o
            LEFT JOIN product_versions pv ON o.product_version_id = pv.id
            LEFT JOIN products p ON pv.product_id = p.id
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN user_full_data ufd ON u.id = ufd.user_id
            WHERE o.id = ?
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Response::error(404, "Order not found");
        } else {
            Response::success($order, "Order retrieved successfully");
        }
    } catch (Exception $e) {
        Response::error(500, "An error occurred while retrieving the order", ["error" => $e->getMessage()]);
    }
}

function addOrder($data) {
    try {
        $pdo = getDatabaseConnection();

        $query = "
            INSERT INTO orders (product_version_id, user_id, quantity, total_price, status, payment_status, date_creation, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $data['product_version_id'],
            $data['user_id'],
            $data['quantity'],
            $data['total_price'],
            $data['status'],
            $data['payment_status']
        ]);

        Response::success(null, "Order created successfully");
    } catch (Exception $e) {
        Response::error(500, "An error occurred while creating the order", ["error" => $e->getMessage()]);
    }
}

function updateOrder($id, $data) {
    try {
        $pdo = getDatabaseConnection();

        // Разрешённые поля для обновления
        $allowedFields = ['status', 'payment_status'];

        // Допустимые значения для status и payment_status
        $validStatuses = ['pending', 'shipped', 'delivered', 'cancelled', 'in_progress'];
        $validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

        // Фильтруем входные данные, оставляя только разрешённые поля
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        // Проверка на наличие допустимых значений
        if (isset($updateData['status']) && !in_array($updateData['status'], $validStatuses, true)) {
            Response::error(400, "Invalid status value");
            return;
        }

        if (isset($updateData['payment_status']) && !in_array($updateData['payment_status'], $validPaymentStatuses, true)) {
            Response::error(400, "Invalid payment status value");
            return;
        }

        if (empty($updateData)) {
            Response::error(400, "No valid fields provided for update");
            return;
        }

        // Динамическое формирование SQL-запроса
        $setClause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updateData)));

        $query = "UPDATE orders SET $setClause, updated_at = NOW() WHERE id = :id";

        $stmt = $pdo->prepare($query);

        // Добавляем ID в массив данных
        $updateData['id'] = $id;

        $stmt->execute($updateData);

        Response::success(null, "Order updated successfully");
    } catch (Exception $e) {
        // Логирование ошибки
        error_log("Update order error: " . $e->getMessage());
        Response::error(500, "An error occurred while updating the order", ["error" => $e->getMessage()]);
    }
}


function deleteOrder($id) {
    try {
        $pdo = getDatabaseConnection();

        $query = "DELETE FROM orders WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);

        Response::success(null, "Order deleted successfully");
    } catch (Exception $e) {
        Response::error(500, "An error occurred while deleting the order", ["error" => $e->getMessage()]);
    }
}

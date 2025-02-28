<?php
// user-orders.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Возвращает список заказов для указанного пользователя.
 * Ожидается, что в GET-параметрах передан user_id.
 */
function getUserOrders() {
    if (!isset($_GET['user_id'])) {
        Response::error(400, "Параметр user_id обязателен");
        exit;
    }
    $user_id = $_GET['user_id'];

    try {
        $pdo = getDatabaseConnection();
        $query = "SELECT id, product_version_id, quantity, total_price, status, payment_status, date_creation
                  FROM orders
                  WHERE user_id = ?
                  ORDER BY date_creation DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success($orders, "Заказы пользователя успешно получены");
    } catch (Exception $e) {
        Response::error(500, "Ошибка при получении заказов", ["error" => $e->getMessage()]);
    }
}

/**
 * Возвращает конкретный заказ пользователя по ID.
 * Для безопасности также ожидается, что в GET-параметрах передан user_id.
 */
function getUserOrderById($orderId) {
    if (!isset($_GET['user_id'])) {
        Response::error(400, "Параметр user_id обязателен");
        exit;
    }
    $user_id = $_GET['user_id'];

    try {
        $pdo = getDatabaseConnection();
        $query = "SELECT id, product_version_id, quantity, total_price, status, payment_status, date_creation
                  FROM orders
                  WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$orderId, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            Response::success($order, "Заказ успешно получен");
        } else {
            Response::error(404, "Заказ не найден или доступ запрещён");
        }
    } catch (Exception $e) {
        Response::error(500, "Ошибка при получении заказа", ["error" => $e->getMessage()]);
    }
}

/**
 * Оформляет новый заказ.
 * Ожидается, что тело запроса (JSON) содержит следующие поля:
 * - product_id: идентификатор продукта из таблицы products.
 * - user_id: идентификатор пользователя, оформляющего заказ.
 * - quantity: количество заказываемого товара.
 *
 * В процессе:
 * 1. По product_id извлекаются данные о продукте из таблицы products (а также цена из product_price).
 * 2. Данные из продукта копируются в таблицу product_versions, создавая таким образом новую версию продукта.
 * 3. На основе цены и количества рассчитывается total_price.
 * 4. Создаётся заказ с предустановленными значениями status ('in_progress') и payment_status ('pending').
 */
function addUserOrder($data) {
    // Обязательные поля
    $requiredFields = ['product_id', 'user_id', 'quantity'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            Response::error(400, "Поле '$field' обязательно для заполнения");
            exit;
        }
    }

    $product_id = $data['product_id'];
    $user_id    = $data['user_id'];
    $quantity   = $data['quantity'];

    try {
        $pdo = getDatabaseConnection();

        // 1. Получаем данные о продукте и его цене.
        $query = "SELECT p.id, p.name, p.description, pp.price 
                  FROM products p
                  LEFT JOIN product_price pp ON p.id = pp.product_id
                  WHERE p.id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            Response::error(404, "Продукт не найден");
            exit;
        }
        if (!isset($product['price']) || $product['price'] <= 0) {
            Response::error(400, "Цена продукта не установлена или неверна");
            exit;
        }

        // 2. Копируем данные из продукта в product_versions.
        // Используем имя и описание из таблицы products.
        $versionName = $product['name'];
        $versionDescription = $product['description'];
        $queryVersion = "INSERT INTO product_versions (product_id, name, description, date_creation, updated_at)
                         VALUES (?, ?, ?, NOW(), NOW())";
        $stmtVersion = $pdo->prepare($queryVersion);
        $stmtVersion->execute([$product_id, $versionName, $versionDescription]);
        $product_version_id = $pdo->lastInsertId();

        // 3. Рассчитываем итоговую цену заказа.
        $price = $product['price'];
        $total_price = $price * $quantity;

        // 4. Устанавливаем значения по умолчанию для статусов заказа.
        $status = 'in_progress';
        $payment_status = 'pending';

        // 5. Создаем заказ.
        $queryOrder = "INSERT INTO orders (product_version_id, user_id, quantity, total_price, status, payment_status, date_creation, updated_at)
                       VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtOrder = $pdo->prepare($queryOrder);
        $stmtOrder->execute([$product_version_id, $user_id, $quantity, $total_price, $status, $payment_status]);

        Response::success(null, "Заказ успешно оформлен");
    } catch (Exception $e) {
        Response::error(500, "Ошибка при оформлении заказа", ["error" => $e->getMessage()]);
    }
}

/**
 * Позволяет пользователю только отменить заказ,
 * то есть обновить статус на "cancelled".
 * Для безопасности ожидается наличие GET-параметра user_id.
 */
function updateUserOrder($orderId, $data) {
    if (!isset($data['status']) || $data['status'] !== 'cancelled') {
        Response::error(400, "Пользователь может только отменить заказ (установить статус 'cancelled')");
        exit;
    }
    if (!isset($_GET['user_id'])) {
        Response::error(400, "Параметр user_id обязателен");
        exit;
    }
    $user_id = $_GET['user_id'];

    try {
        $pdo = getDatabaseConnection();
        // Проверяем, что заказ принадлежит указанному пользователю
        $checkQuery = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([$orderId, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            Response::error(403, "Изменение данного заказа не разрешено");
            exit;
        }

        $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$data['status'], $orderId]);
        Response::success(null, "Заказ успешно обновлён");
    } catch (Exception $e) {
        Response::error(500, "Ошибка при обновлении заказа", ["error" => $e->getMessage()]);
    }
}
?>

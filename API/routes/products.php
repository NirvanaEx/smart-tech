<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/Response.php';

// Получение полного списка товаров
function getProducts() {
    $pdo = getDatabaseConnection();
    $config = include __DIR__ . '/../config/path.php';
    $baseImagePath = $config['base_image_path'];

    // Начальные условия (только не удалённые товары)
    $conditions = ["products.data_status != 'deleted'"];
    $params = [];

    // Фильтрация по категории
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $conditions[] = "category.name = :category";
        $params['category'] = $_GET['category'];
    }

    // Фильтрация по подкатегории
    if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
        $conditions[] = "subcategory.name = :subcategory";
        $params['subcategory'] = $_GET['subcategory'];
    }

    // Поиск по названию и описанию
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $conditions[] = "(products.name LIKE :search OR products.description LIKE :search)";
        $params['search'] = '%' . $_GET['search'] . '%';
    }

    $whereClause = implode(" AND ", $conditions);

    try {
        $query = "
            SELECT 
                products.id, 
                products.name AS product_name, 
                products.description, 
                products.quantity, 
                products.date_creation, 
                products.updated_at, 
                products.data_status,
                products.image_path, 
                CONCAT(:base_path, products.image_path) AS image_url, 
                subcategory.name AS subcategory_name,
                category.name AS category_name, 
                IFNULL((SELECT price FROM product_price WHERE product_id = products.id ORDER BY date_creation DESC LIMIT 1), 0) AS price,
                (SELECT COUNT(*) FROM orders WHERE product_version_id IN 
                    (SELECT id FROM product_versions WHERE product_id = products.id)) AS order_count
            FROM products
            INNER JOIN subcategory ON products.subcategory_id = subcategory.id
            INNER JOIN category ON subcategory.category_id = category.id
            WHERE {$whereClause}
            ORDER BY products.id
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':base_path', $baseImagePath, PDO::PARAM_STR);
        // Привязываем остальные параметры
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $products = $stmt->fetchAll();
        Response::send(200, "Products fetched successfully", $products);
    } catch (PDOException $e) {
        Response::send(500, "Failed to fetch products: " . $e->getMessage());
    }
}


// Создание нового товара
function addProduct() {
    $pdo = getDatabaseConnection();
    $config = include __DIR__ . '/../config/path.php';

    $baseImagePath = rtrim($config['base_image_path'], '/');
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . parse_url($baseImagePath, PHP_URL_PATH);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (empty($_POST['subcategory_id']) || empty($_POST['name']) || empty($_POST['description']) || !isset($_POST['quantity']) || empty($_POST['price']) || empty($_FILES['image'])) {
        Response::send(400, "Missing required fields: subcategory_id, name, description, quantity, price, image");
    }

    $image = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png'];
    if (!in_array($image['type'], $allowedTypes)) {
        Response::send(400, "Invalid image format. Only JPG and PNG are allowed");
    }

    if ($image['size'] > 1 * 1024 * 1024) {
        Response::send(400, "Image size must not exceed 1 MB");
    }

    // Генерация уникального имени изображения
    $extension = pathinfo($image['name'], PATHINFO_EXTENSION); // Получение расширения файла
    $uniqueName = uniqid() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $destination = $uploadDir . '/' . $uniqueName;

    if (!move_uploaded_file($image['tmp_name'], $destination)) {
        Response::send(500, "Failed to upload image");
    }

    $imageUrl = $baseImagePath . '/' . $uniqueName;

    try {
        $pdo->beginTransaction();

        // Добавляем товар
        $stmt = $pdo->prepare("
            INSERT INTO products (subcategory_id, name, description, quantity, data_status, image_path, date_creation, updated_at) 
            VALUES (:subcategory_id, :name, :description, :quantity, 'available', :image_path, NOW(), NOW())
        ");
        $stmt->execute([
            'subcategory_id' => $_POST['subcategory_id'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'quantity' => $_POST['quantity'],
            'image_path' => $uniqueName
        ]);
        $productId = $pdo->lastInsertId();

        // Добавляем цену
        $stmt = $pdo->prepare("
            INSERT INTO product_price (product_id, price, date_creation, updated_at) 
            VALUES (:product_id, :price, NOW(), NOW())
        ");
        $stmt->execute([
            'product_id' => $productId,
            'price' => $_POST['price']
        ]);

        $pdo->commit();
        Response::send(201, "Product added successfully", ["image_url" => $imageUrl]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        Response::send(500, "Failed to add product: " . $e->getMessage());
    }
}




// Поиск товаров
function searchProducts($query) {
    $pdo = getDatabaseConnection();
    $config = include __DIR__ . '/../config/path.php';
    $baseImagePath = $config['base_image_path'];

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
                products.image_path, 
                CONCAT(:base_path, products.image_path) AS image_url, 
                subcategory.name AS subcategory_name
            FROM products
            INNER JOIN subcategory ON products.subcategory_id = subcategory.id
            WHERE products.name LIKE :query OR products.description LIKE :query
            ORDER BY products.id
        ");
        $stmt->bindValue(':base_path', $baseImagePath, PDO::PARAM_STR);
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

    try {
        // Проверяем, существует ли товар
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::send(404, "Product not found");
        }

        // Изменяем статус на "deleted"
        $stmt = $pdo->prepare("
            UPDATE products 
            SET data_status = 'deleted', updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);

        Response::send(200, "Product status updated to 'deleted'");
    } catch (PDOException $e) {
        Response::send(500, "Failed to update product status: " . $e->getMessage());
    }
}
// Обновление товара
function updateProduct($id, $data) {
    $pdo = getDatabaseConnection();
    $config = include __DIR__ . '/../config/path.php';
    $baseImagePath = rtrim($config['base_image_path'], '/');
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . parse_url($baseImagePath, PHP_URL_PATH);

    try {
        $stmt = $pdo->prepare("SELECT id, image_path FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::send(404, "Product not found");
        }

        $fields = [];
        $params = ['id' => $id];

        // Лог входящих данных
        error_log("Received data: " . print_r($data, true));

        // Обновляем текстовые поля
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }

        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params['description'] = $data['description'];
        }

        if (isset($data['quantity'])) {
            $fields[] = "quantity = :quantity";
            $params['quantity'] = $data['quantity'];
        }

        if (isset($data['data_status'])) {
            $fields[] = "data_status = :data_status";
            $params['data_status'] = $data['data_status'];
        }

        // Проверяем и сохраняем новую картинку
        if (!empty($_FILES['image'])) {
            $image = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png'];

            if (!in_array($image['type'], $allowedTypes)) {
                Response::send(400, "Invalid image format");
            }

            $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '.' . $extension;
            $destination = $uploadDir . '/' . $uniqueName;

            if (!move_uploaded_file($image['tmp_name'], $destination)) {
                Response::send(500, "Failed to upload image");
            }

            $fields[] = "image_path = :image_path";
            $params['image_path'] = $uniqueName;

            // Удаляем старую картинку
            if (!empty($product['image_path'])) {
                unlink($uploadDir . '/' . $product['image_path']);
            }
        }

        // Обновляем данные в таблице products
        if (!empty($fields)) {
            $fields[] = "updated_at = NOW()";
            $query = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = :id";

            error_log("Executing query: " . $query);
            error_log("With params: " . print_r($params, true));

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        }

        // Обновляем цену в таблице product_price
        if (isset($data['price'])) {
            $stmt = $pdo->prepare("
                INSERT INTO product_price (product_id, price, date_creation, updated_at)
                VALUES (:product_id, :price, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                price = VALUES(price), updated_at = NOW()
            ");
            $stmt->execute([
                'product_id' => $id,
                'price' => $data['price'],
            ]);
        }

        Response::send(200, "Product updated successfully");
    } catch (PDOException $e) {
        error_log("PDOException: " . $e->getMessage());
        Response::send(500, "Failed to update product: " . $e->getMessage());
    }
}

// Получение данных товара по ID
function getProductById($id) {
    $pdo = getDatabaseConnection();
    $config = include __DIR__ . '/../config/path.php';
    $baseImagePath = $config['base_image_path'];

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
                products.image_path, 
                CONCAT(:base_path, products.image_path) AS image_url, 
                subcategory.name AS subcategory_name,
                category.name AS category_name, 
                IFNULL((SELECT price FROM product_price WHERE product_id = products.id ORDER BY date_creation DESC LIMIT 1), 0) AS price
            FROM products
            INNER JOIN subcategory ON products.subcategory_id = subcategory.id
            INNER JOIN category ON subcategory.category_id = category.id
            WHERE products.id = :id AND products.data_status != 'deleted'
        ");
        $stmt->bindValue(':base_path', $baseImagePath, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch();
        if ($product) {
            Response::send(200, "Product fetched successfully", $product);
        } else {
            Response::send(404, "Product not found");
        }
    } catch (PDOException $e) {
        Response::send(500, "Failed to fetch product: " . $e->getMessage());
    }
}

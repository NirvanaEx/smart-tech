<?php

require_once __DIR__ . '/routes/users.php';
require_once __DIR__ . '/routes/category.php';
require_once __DIR__ . '/routes/subcategory.php';
require_once __DIR__ . '/routes/products.php';
require_once __DIR__ . '/routes/cart.php';
require_once __DIR__ . '/routes/favorite-products.php';
require_once __DIR__ . '/routes/auth.php';




require_once __DIR__ . '/helpers/Response.php';

// Получение данных из входящего запроса
$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$resource = $path[1] ?? null; // Пример: /users или /categories
$id = $path[2] ?? null; // Пример: /users/1 или /categories/2

header('Content-Type: application/json');

// Обработка маршрутов
if ($resource === 'users') {
    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                Response::send(400, "Fetching a single user is not implemented yet");
            } else {
                getUsers();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            addUser($data);
            break;
        case 'PUT':
            if (!$id) {
                Response::send(400, "User ID is required for update");
            }
            $data = json_decode(file_get_contents('php://input'), true);
            updateUser($id, $data);
            break;
        default:
            Response::send(405, "Method not allowed");
    }
}
elseif ($resource === 'auth') {
    switch ($requestMethod) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['action'])) {
                if ($data['action'] === 'register') {
                    registerUser($data);
                } elseif ($data['action'] === 'login') {
                    loginUser($data);
                } elseif ($data['action'] === 'full_data') {
                    getUserData($data);
                } else {
                    Response::send(400, "Invalid action");
                }
            } else {
                Response::send(400, "Action is required");
            }
            break;

        case 'PUT': // Добавляем PUT для обновления данных
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['action']) && $data['action'] === 'update') {
                updateUserData($data);
            } else {
                Response::send(400, "Invalid or missing action");
            }
            break;

        default:
            Response::send(405, "Method not allowed");
    }
}
elseif ($resource === 'categories') {
    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                Response::send(400, "Fetching a single category is not implemented yet");
            } else {
                getCategories();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            addCategory($data);
            break;
        case 'PUT':
            if (!$id) {
                Response::send(400, "Category ID is required for update");
            }
            $data = json_decode(file_get_contents('php://input'), true);
            updateCategory($id, $data);
            break;
        case 'DELETE':
            if (!$id) {
                Response::send(400, "Category ID is required for deletion");
            }
            deleteCategory($id);
            break;
        default:
            Response::send(405, "Method not allowed");
    }
}
elseif ($resource === 'subcategories') {
    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                Response::send(400, "Fetching a single subcategory is not implemented yet");
            } else {
                getSubcategories();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            addSubcategory($data);
            break;
        case 'PUT':
            if (!$id) {
                Response::send(400, "Subcategory ID is required for update");
            }
            $data = json_decode(file_get_contents('php://input'), true);
            updateSubcategory($id, $data);
            break;
        case 'DELETE':
            if (!$id) {
                Response::send(400, "Subcategory ID is required for deletion");
            }
            deleteSubcategory($id);
            break;
        default:
            Response::send(405, "Method not allowed");
    }
}
elseif ($resource === 'products') {
    switch ($requestMethod) {
        case 'GET':
            if (isset($_GET['query'])) {
                // Если передан параметр поиска
                searchProducts($_GET['query']);
            } elseif (!empty($id)) {
                // Получение товара по ID
                getProductById($id);
            } else {
                // Получение полного списка товаров
                getProducts();
            }
            break;

        case 'POST':
            if (!empty($id)) {
                updateProduct($id, $_POST); // Обновление товара
            } else {
                addProduct(); // Добавление нового товара
            }
            break;

        case 'DELETE':
            if (empty($id)) {
                Response::send(400, "Product ID is required for deletion");
            }
            deleteProduct($id);
            break;

        default:
            Response::send(405, "Method not allowed");
    }
}
elseif ($resource === 'cart') {
    switch ($requestMethod) {
        case 'GET':
            if (!$id) {
                Response::send(400, "User ID is required");
            }
            getCart($id);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            addToCart($data);
            break;

        case 'PUT':
            if (!$id) {
                Response::send(400, "Cart item ID is required for update");
            }
            $data = json_decode(file_get_contents('php://input'), true);
            updateCartItem($id, $data);
            break;

        case 'DELETE':
            if (!$id) {
                Response::send(400, "Cart item ID is required for deletion");
            }
            deleteCartItem($id);
            break;

        default:
            Response::send(405, "Method not allowed");
    }
}
elseif ($resource === 'favorite-products') {
    switch ($requestMethod) {
        case 'GET':
            if (!$id) {
                Response::send(400, "User ID is required");
            }
            getFavoriteProducts($id);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            addFavoriteProduct($data);
            break;

        case 'DELETE':
            if (!$id) {
                Response::send(400, "Favorite product ID is required for deletion");
            }
            deleteFavoriteProduct($id);
            break;

        default:
            Response::send(405, "Method not allowed");
    }
}
else {
    Response::send(404, "Resource not found");
}

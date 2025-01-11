<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART.INC</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Swiper.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .banner {
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            text-align: center;
            color: white;
        }
        footer {
            margin-top: auto;
        }

        /* Banner   */
        .banner {
            position: relative;
            border-radius: 20px;
            overflow: hidden; /* Сохраняем скругление */
        }

        .banner img {
            width: 100%;
            height: auto;
            display: block;
        }

        .banner-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.4); /* Полупрозрачный фон */
            padding: 20px;
            border-radius: 10px; /* Скругление контейнера */
            color: white;
            text-align: center;
        }

        .banner-text h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .banner-text p {
            margin-bottom: 15px;
        }

        .banner-text .btn {
            color: #fff;
            border: 1px solid #fff;
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
            background-color: transparent;
            transition: background-color 0.3s, color 0.3s;
        }

        .banner-text .btn:hover {
            background-color: #fff;
            color: #000;
        }


        /* Эффект наведения на логотип */
        .logo-link {
            text-decoration: none; /* Убираем подчеркивание */
            color: inherit; /* Используем текущий цвет текста */
            transition: color 0.3s ease; /* Плавный переход цвета */
        }

        .logo-link:hover {
            color: #6c7cdb; /* Цвет текста при наведении */
        }

        .logo-link img {
            transition: transform 0.3s ease; /* Плавный переход масштаба */
        }

        .logo-link:hover img {
            transform: scale(1.1); /* Увеличиваем масштаб изображения при наведении */
        }

    </style>
</head>
<body class="bg-dark text-light">
<!-- Header -->
<header class="bg-secondary py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="#" class="d-flex align-items-center logo-link" id="logo-link">
            <img src="upload/logo-placeholder.png" alt="Logo" style="height: 40px;" class="me-3">
            <span>SMART.INC</span>
        </a>

        <nav class="d-flex gap-3 align-items-center">
            <button class="btn btn-outline-light"><i class="fas fa-percent"></i> Акции</button>

            <!-- Контейнер для кнопки "Каталог" и выпадающего списка -->
            <div class="dropdown-catalog">
                <button id="catalogButton" class="btn btn-outline-light">
                    <i class="fas fa-th"></i> Каталог
                </button>
                <!-- Скрытый список категорий -->
                <select id="categories" style="width: 300px; display: none;">
                    <optgroup label="Телевизоры">
                        <option value="4K">4K</option>
                        <option value="LED">LED</option>
                        <option value="OLED">OLED</option>
                    </optgroup>
                    <optgroup label="Телефоны">
                        <option value="Смартфоны">Смартфоны</option>
                        <option value="Кнопочные">Кнопочные</option>
                    </optgroup>
                    <optgroup label="Ноутбуки">
                        <option value="Игровые">Игровые</option>
                        <option value="Для работы">Для работы</option>
                    </optgroup>
                </select>
            </div>

            <button class="btn btn-outline-light position-relative" onclick="showCart()">
                <i class="fas fa-shopping-cart"></i> Корзина
                <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </button>
            <input type="text" class="form-control" placeholder="Напишите название товара...">
            <button class="btn btn-outline-light" data-page="favorites" data-title="Избранное">
                <i class="fas fa-heart"></i> Избранное
            </button>
            <button class="btn btn-outline-light"><i class="fas fa-exchange-alt"></i> Сравнить</button>
            <button class="btn btn-outline-light"><i class="fas fa-sign-in-alt"></i> Войти</button>
        </nav>
    </div>
</header>

<!-- Main Container -->
<main class="container my-4" id="main-container">
    <!-- Banner -->
    <div class="banner bg-dark">
        <img src="upload/banner-placeholder.jpg" alt="Banner">
        <div class="banner-text">
            <h1>SMART.INC SUPER SALE</h1>
            <a href="#" class="btn" data-page="all" data-title="Все товары">Shop Now</a>
        </div>
    </div>
    <!-- Новинки -->
    <section class="my-5" id="dynamic-content">
        <h2 class="text-light" id="dynamic-content-title">Новинки</h2>
        <!-- Навигация -->

        <!-- Контейнер для динамической загрузки -->
        <div id="dynamic-content-area">
            <div class="text-center text-light">Загрузка...</div>
        </div>
    </section>
</main>

<!-- Footer -->
<footer class="bg-secondary py-4 text-center text-light">
    <p>&copy; 2024 SMART.INC</p>
</footer>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<!-- Swiper.js JS -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>

    function showCart() {
        // Создание HTML для отображения корзины
        let cartHTML = '<table class="table table-dark table-striped">';
        cartHTML += '<thead><tr><th>Изображение</th><th>Название</th><th>Цена</th><th>Количество</th><th>Действия</th></tr></thead>';
        cartHTML += '<tbody>';

        let total = 0;

        if (cart.length > 0) {
            cart.forEach(product => {
                cartHTML += `<tr>
                    <td><img src="${product.image}" alt="${product.name}" style="height: 50px;"></td>
                    <td>${product.name}</td>
                    <td>${product.price} ₽</td>
                    <td>${product.quantity}</td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="addToCart({ id: '${product.id}', name: '${product.name}', price: ${product.price}, image: '${product.image}' })">+</button>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart('${product.id}')">-</button>
                    </td>
                </tr>`;

                total += product.price * product.quantity;
            });
        } else {
            cartHTML += '<tr><td colspan="5" class="text-center">Корзина пуста</td></tr>';
        }

        cartHTML += '</tbody></table>';
        cartHTML += `<div class="text-end fw-bold">Итого: ${total} ₽</div>`;

        // Открытие SweetAlert окна
        Swal.fire({
            title: 'Корзина',
            html: cartHTML,
            showCancelButton: true,
            confirmButtonText: 'Оформить заказ',
            cancelButtonText: 'Закрыть',
            showDenyButton: true,
            denyButtonText: 'Очистить корзину',
            customClass: {
                popup: 'bg-dark text-light'
            },
            preConfirm: () => {
                if (cart.length === 0) {
                    Swal.fire('Корзина пуста', '', 'warning');
                    return false;
                }
                return true;
            }
        }).then(result => {
            if (result.isConfirmed) {
                processOrder();
            } else if (result.isDenied) {
                clearCart();
            }
        });
    }

    // Функция для обработки заказа
    function processOrder() {
        // Пример обработки заказа
        Swal.fire('Заказ оформлен!', '', 'success');
        clearCart();
    }

    // Функция для очистки корзины
    function clearCart() {
        cart.length = 0;
        updateCartCount();
        Swal.fire('Корзина очищена!', '', 'success');
    }

    // Глобальный массив для избранных товаров
    const favorites = [];

    // Функция для добавления товара в избранное
    function addToFavorites(product) {
        const existingProduct = favorites.find(item => item.id === product.id);
        if (!existingProduct) {
            favorites.push(product);
        }
    }

    // Функция для удаления товара из избранного
    function removeFromFavorites(productId) {
        const productIndex = favorites.findIndex(item => item.id === productId);
        if (productIndex !== -1) {
            favorites.splice(productIndex, 1);
        }
    }

    // Функция для проверки, находится ли товар в избранном
    function isFavorite(productId) {
        return favorites.some(item => item.id === productId);
    }

    // Глобальная корзина
    const cart = [];

    // Обновление количества товаров в значке корзины
    function updateCartCount() {
        const cartCountElement = document.getElementById("cart-count");
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCountElement.textContent = totalItems;
    }

    // Функция добавления товара в корзину
    function addToCart(product) {
        const existingProduct = cart.find(item => item.id === product.id);
        if (existingProduct) {
            existingProduct.quantity++;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        updateCartCount();
    }

    // Функция уменьшения количества товара
    function removeFromCart(productId) {
        const product = cart.find(item => item.id === productId);
        if (product) {
            product.quantity--;
            if (product.quantity === 0) {
                cart.splice(cart.indexOf(product), 1);
            }
        }
        updateCartCount();
    }

    document.addEventListener("DOMContentLoaded", function () {
        const contentArea = document.getElementById("dynamic-content-area");

        // Обработчик для перезагрузки страницы при нажатии на логотип
        const logoLink = document.getElementById("logo-link");
        logoLink.addEventListener("click", function (event) {
            event.preventDefault(); // Предотвращаем переход по ссылке
            window.location.reload(); // Перезагружаем страницу
        });


        // Функция для загрузки контента
        function loadContent(page, title) {
            contentArea.innerHTML = '<div class="text-center text-light">Загрузка...</div>';

            fetch(`pages/${page}.php`)
                .then(response => {
                    if (!response.ok) throw new Error("Ошибка загрузки страницы");
                    return response.text();
                })
                .then(html => {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;

                    // Вставляем HTML контент в контейнер
                    contentArea.innerHTML = tempDiv.innerHTML;

                    // Выполняем встроенные скрипты
                    const scripts = tempDiv.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        if (script.src) {
                            // Если скрипт внешний
                            newScript.src = script.src;
                        } else {
                            // Если скрипт встроенный
                            newScript.textContent = script.textContent;
                        }
                        document.body.appendChild(newScript);
                    });

                    // Обновляем заголовок
                    document.getElementById("dynamic-content-title").textContent = title || "Заголовок";
                })
                .catch(error => {
                    contentArea.innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
                });
        }

        // Обработка кликов на элементы с атрибутом data-page
        document.addEventListener('click', function (event) {
            const target = event.target.closest('[data-page]');
            if (target) {
                event.preventDefault();
                const page = target.getAttribute('data-page');
                const title = target.getAttribute('data-title');
                loadContent(page, title);
            }
        });

        // Загрузка страницы по умолчанию
        loadContent('new', 'Новинки');
    });

</script>
</body>
</html>

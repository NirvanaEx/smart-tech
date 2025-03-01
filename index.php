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
    <!-- jsTree CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
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
        /* Banner */
        .banner {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
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
            background-color: rgba(0, 0, 0, 0.4);
            padding: 20px;
            border-radius: 10px;
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
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }
        .logo-link:hover {
            color: #6c7cdb;
        }
        .logo-link img {
            transition: transform 0.3s ease;
        }
        .logo-link:hover img {
            transform: scale(1.1);
        }
        /* Стили для контейнера дерева */
        #categoryTreeContainer {
            max-height: 400px;
            overflow-y: auto;
            background-color: #343a40;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
        }
        /* Убираем стандартные иконки jsTree, чтобы использовать Font Awesome */
        .jstree-icon { display: none; }

        /* Стили для избранного */
        .fav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .fav-card {
            background: #333;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .fav-card img {
            max-width: 100%;
            border-radius: 5px;
        }
        .fav-btn {
            cursor: pointer;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
        }
        .fav-btn-add {
            background: #007bff;
            color: #fff;
        }
        .fav-btn-add:hover {
            background: #0056b3;
        }
        .fav-quantity-controls {
            display: flex;
            align-items: center;
        }
        .fav-quantity-controls button {
            padding: 5px;
        }
        .fav-quantity-value {
            margin: 0 10px;
        }
        .fav-btn-favorite {
            background: transparent;
            border: 1px solid red;
            color: red;
            padding: 3px 6px;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 10px;
        }
        .fav-cart-controls-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .fav-cart-controls {
            flex: 1;
            text-align: right;
        }
    </style>
    <script>
        const BASE_URL = 'http://smart-tech/API/';
    </script>
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
            <!-- Кнопка для открытия древовидного меню каталога -->
            <button type="button" id="catalogButton" class="btn btn-outline-light">
                <i class="fas fa-th"></i> Каталог
            </button>
            <!-- Контейнер для древовидного меню каталога -->
            <div id="categoryTreeContainer" class="dropdown-menu" style="display: none; position: absolute; z-index: 1000; min-width: 300px;"></div>
            <button id="cartButton" class="btn btn-outline-light position-relative">
                <i class="fas fa-shopping-cart"></i> Корзина
                <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </button>
            <!-- Поле поиска -->
            <input type="text" id="searchInput" class="form-control" placeholder="Напишите название товара...">
            <!-- Ссылка на избранное теперь с data-page="favorites" -->
            <a href="#" data-page="favorites" class="btn btn-outline-light"><i class="fas fa-heart"></i> Избранное</a>
            <button class="btn btn-outline-light"><i class="fas fa-exchange-alt"></i> Сравнить</button>
            <!-- Кнопка "Войти" -->
            <div id="auth-container">
                <button id="loginButton" class="btn btn-outline-light">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </div>
        </nav>
    </div>
</header>

<!-- Main Container -->
<main class="container my-4" id="main-container">
    <!-- Default контейнер: баннер + новинки -->
    <div id="default-container">
        <!-- Banner -->
        <div class="banner bg-dark">
            <img src="upload/banner-placeholder.jpg" alt="Banner">
            <div class="banner-text">
                <h1>SMART.INC SUPER SALE</h1>
                <!-- При нажатии на Shop Now переключаемся на динамический контейнер -->
                <a href="#" class="btn" id="shopNowButton">Shop Now</a>
            </div>
        </div>
        <!-- Контейнер для новых товаров (new.php) -->
        <section class="my-5" id="new-products-container">
            <h2 class="text-light" id="default-title">Новинки</h2>
            <div id="new-products-area">
                <!-- Контейнер для карусели -->
                <div class="swiper">
                    <div class="swiper-wrapper" id="newProductsWrapper">
                        <!-- Слайды будут загружены через new-products.js -->
                    </div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
    </div>

    <!-- Dynamic контейнер: для фильтров, поиска, категорий, избранного и т.д. -->
    <div id="dynamic-content-container" style="display: none;">
        <!-- Заголовок динамического контента -->
        <h2 class="text-light" id="dynamic-content-title"></h2>
        <div id="dynamic-content-area">
            <div class="text-center text-light">Загрузка...</div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-secondary py-4 text-center text-light">
    <p>&copy; 2024 SMART.INC</p>
</footer>

<!-- Скрипты -->
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<!-- Swiper.js JS -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<!-- jsTree JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
<script type="module" src="js/part-index-auth.js"></script>
<script src="js/part-index-cart.js"></script>

<script>
    // Глобальные переменные для фильтров
    let currentCategory = '';
    let currentSubcategory = '';
    let currentSearch = '';

    // Функция загрузки динамического контента (например, all.php)
    function loadDynamicContent() {
        let url = 'all.php?';
        if (currentCategory) {
            url += 'category=' + encodeURIComponent(currentCategory) + '&';
        }
        if (currentSubcategory) {
            url += 'subcategory=' + encodeURIComponent(currentSubcategory) + '&';
        }
        if (currentSearch) {
            url += 'search=' + encodeURIComponent(currentSearch) + '&';
        }
        url = url.slice(0, -1);

        // Переключаем контейнеры: скрываем default, показываем dynamic
        document.getElementById("default-container").style.display = "none";
        document.getElementById("dynamic-content-container").style.display = "block";

        const contentArea = document.getElementById("dynamic-content-area");
        contentArea.innerHTML = '<div class="text-center text-light">Загрузка...</div>';
        fetch(`pages/${url}`)
            .then(response => {
                if (!response.ok) throw new Error("Ошибка загрузки страницы");
                return response.text();
            })
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                contentArea.innerHTML = tempDiv.innerHTML;
                const scripts = tempDiv.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript);
                });
                // Синхронизация корзины и избранного
                const userId = getUserId();
                if (userId) {
                    fetchCart(userId);
                    if (typeof fetchFavorites === 'function') {
                        fetchFavorites();
                    }
                }
            })
            .catch(error => {
                contentArea.innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
            });
    }

    // Функция загрузки новых товаров (new.php) в default контейнер
    function loadNewProducts() {
        const newArea = document.getElementById("new-products-area");
        newArea.innerHTML = '<div class="text-center text-light">Загрузка...</div>';
        fetch(`pages/new.php`)
            .then(response => {
                if (!response.ok) throw new Error("Ошибка загрузки страницы");
                return response.text();
            })
            .then(html => {
                newArea.innerHTML = html;
            })
            .catch(error => {
                newArea.innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
            });
    }

    // Функция загрузки страницы избранного (интегрированного в index.php)
    function loadFavoritesContent() {
        // Переключаем контейнеры
        document.getElementById("default-container").style.display = "none";
        document.getElementById("dynamic-content-container").style.display = "block";
        // Вставляем HTML-разметку для избранного
        document.getElementById("dynamic-content-area").innerHTML = `
        <div class="fav-container my-4">
            <div id="fav-favorites-container"></div>
        </div>
    `;
        // Инициализируем скрипты для избранного
        initFavorites();
    }

    // Обработчик кликов для элементов с data-page (сброс фильтров)
    document.addEventListener('click', function (event) {
        const target = event.target.closest('[data-page]');
        if (target) {
            event.preventDefault();
            currentCategory = '';
            currentSubcategory = '';
            currentSearch = '';
            // Если выбран favorites – вызываем нашу функцию загрузки избранного
            const page = target.getAttribute('data-page');
            if (page === 'favorites') {
                loadFavoritesContent();
                return;
            }
            document.getElementById("default-container").style.display = "none";
            document.getElementById("dynamic-content-container").style.display = "block";
            const title = target.getAttribute('data-title');
            fetch(`pages/${page}.php`)
                .then(response => {
                    if (!response.ok) throw new Error("Ошибка загрузки страницы");
                    return response.text();
                })
                .then(html => {
                    document.getElementById("dynamic-content-area").innerHTML = html;
                    document.getElementById("dynamic-content-title").textContent = title || "Заголовок";
                })
                .catch(error => {
                    document.getElementById("dynamic-content-area").innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
                });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // Загружаем новинки по умолчанию
        loadNewProducts();

        // Перезагрузка страницы при клике на логотип
        const logoLink = document.getElementById("logo-link");
        logoLink.addEventListener("click", function (event) {
            event.preventDefault();
            window.location.reload();
        });

        // Функция для загрузки данных каталога (категории и подкатегории)
        function loadCategoryTree() {
            Promise.all([
                fetch(`${BASE_URL}categories`).then(res => res.json()),
                fetch(`${BASE_URL}subcategories`).then(res => res.json())
            ]).then(([categoriesData, subcategoriesData]) => {
                if (categoriesData.status !== 200) {
                    console.error("Ошибка загрузки категорий");
                    return;
                }
                const categories = categoriesData.data;
                const subcategories = (subcategoriesData.status === 200) ? subcategoriesData.data : [];
                // Формируем дерево с узлом "Все"
                const treeData = [{
                    id: "all",
                    text: "Все",
                    icon: "fa fa-folder",
                    children: []
                }].concat(
                    categories.map(category => {
                        const children = subcategories
                            .filter(sub => parseInt(sub.category_id) === parseInt(category.id))
                            .map(sub => ({
                                id: `sub-${sub.id}`,
                                text: sub.name,
                                icon: "fa fa-folder"
                            }));
                        return {
                            id: `cat-${category.id}`,
                            text: category.name,
                            icon: "fa fa-folder",
                            children: children
                        };
                    })
                );

                $('#categoryTree').jstree({
                    'core': {
                        'data': treeData,
                        'themes': {
                            'variant': 'large'
                        }
                    }
                });

                // Обработка выбора узла дерева
                $('#categoryTree').on("changed.jstree", function (e, data) {
                    if (data.selected.length) {
                        const node = data.instance.get_node(data.selected[0]);
                        if (node.id === "all") {
                            currentCategory = '';
                            currentSubcategory = '';
                        } else if (node.id.startsWith("cat-")) {
                            currentCategory = node.text;
                            currentSubcategory = '';
                        } else if (node.id.startsWith("sub-")) {
                            const parentNode = data.instance.get_node(data.instance.get_parent(node));
                            currentCategory = parentNode.text;
                            currentSubcategory = node.text;
                        }
                        loadDynamicContent();
                        $("#categoryTreeContainer").hide();
                    }
                });
            }).catch(err => {
                console.error("Ошибка загрузки каталога: ", err);
            });
        }

        // Показываем/скрываем древовидное меню каталога
        const catalogButton = document.getElementById("catalogButton");
        const categoryTreeContainer = document.getElementById("categoryTreeContainer");
        if (!document.getElementById("categoryTree")) {
            const treeDiv = document.createElement("div");
            treeDiv.id = "categoryTree";
            categoryTreeContainer.appendChild(treeDiv);
        }
        catalogButton.addEventListener("click", function (e) {
            e.stopPropagation();
            const rect = catalogButton.getBoundingClientRect();
            categoryTreeContainer.style.top = (rect.bottom) + "px";
            categoryTreeContainer.style.left = rect.left + "px";
            categoryTreeContainer.style.display = "block";
            if ($('#categoryTree').jstree(true) === false) {
                loadCategoryTree();
            }
        });
        document.addEventListener("click", function (e) {
            if (!categoryTreeContainer.contains(e.target) && e.target !== catalogButton) {
                categoryTreeContainer.style.display = "none";
            }
        });

        // Обработчик изменения в поле поиска (не сбрасываем выбранную категорию)
        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("input", function () {
            currentSearch = searchInput.value.trim();
            loadDynamicContent();
        });

        // Обработчик клика по кнопке "Shop Now" – переключение на dynamic контент
        const shopNowButton = document.getElementById("shopNowButton");
        shopNowButton.addEventListener("click", function (e) {
            e.preventDefault();
            currentSearch = '';
            loadDynamicContent();
        });
    });
</script>

<!-- Скрипты для избранного (перенесены из pages/favorites.php) -->
<script>
    // Глобальные переменные для избранного
    let favCart = [];
    let favFavorites = [];

    function favGetUserId() {
        const user = JSON.parse(localStorage.getItem('user'));
        return user ? user.user_id : null;
    }


    function favFetchCart(userId, callback) {
        $.ajax({
            url: BASE_URL + 'cart/' + userId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                favCart = (response.status == 200) ? response.data : [];
                if(callback) callback();
            },
            error: function() {
                favCart = [];
                if(callback) callback();
            }
        });
    }

    function favFetchFavorites(callback) {
        const userId = favGetUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        $.ajax({
            url: BASE_URL + 'favorite-products/' + userId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.status == 200) {
                    favFavorites = response.data;
                    if(callback) callback();
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', `Не удалось получить избранное: ${xhr.status} - ${error}`, 'error');
            }
        });
    }

    // Функция переключения избранного: если товар в избранном, удаляет, иначе добавляет
    function favToggleFavorite(productId) {
        const exists = favFavorites.some(item => parseInt(item.product_id) === productId);
        if (exists) {
            favRemoveFromFavorites(productId);
        } else {
            favAddToFavorites({ id: productId });
        }
    }

    // Функция добавления товара в избранное
    function favAddToFavorites(product) {
        const userId = favGetUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        $.ajax({
            url: BASE_URL + 'favorite-products',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ user_id: userId, product_id: product.id }),
            dataType: 'json',
            success: function(response) {
                if(response.status == 200 || response.status == 201) {
                    Swal.fire('Товар добавлен в избранное', '', 'success');
                    favFetchFavorites(updateFavoriteUI);
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', `Не удалось добавить в избранное: ${xhr.status} - ${error}`, 'error');
            }
        });
    }


    function favLoadFavorites() {
        const container = $('#fav-favorites-container');
        container.empty();
        if (!favFavorites.length) {
            container.html('<p>Нет избранных товаров.</p>');
            return;
        }
        let html = '<div class="row g-4">';
        favFavorites.forEach(product => {
            // Проверяем, есть ли товар в корзине
            const cartItem = favCart.find(item => parseInt(item.product_id) === parseInt(product.product_id));
            let cartControlsHtml = '';
            if (cartItem) {
                cartControlsHtml = `
                    <div class="fav-cart-controls">
                        <div class="fav-quantity-controls d-flex align-items-center">
                            <button class="btn btn-outline-light fav-quantity-decrease" data-fav-cart-id="${cartItem.id}" data-product-id="${product.product_id}">-</button>
                            <span class="fav-quantity-value mx-2" data-fav-cart-id="${cartItem.id}">${cartItem.quantity}</span>
                            <button class="btn btn-outline-light fav-quantity-increase" data-fav-cart-id="${cartItem.id}" data-product-id="${product.product_id}">+</button>
                        </div>
                    </div>
                `;
            } else {
                cartControlsHtml = `
                    <div class="fav-cart-controls">
                        <button class="btn btn-outline-light add-to-cart-btn w-100" data-product-id="${product.product_id}">
                            <i class="fas fa-shopping-bag"></i> В корзину
                        </button>
                    </div>
                `;
            }
            html += `
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card bg-dark text-light h-100">
                        <img src="${product.image_url}" class="card-img-top" alt="${product.product_name}">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${product.product_name}</h5>
                            <p class="card-text">${product.description}</p>
                            <p class="card-text"><strong>Цена:</strong> ${product.price} сум</p>
                            ${cartControlsHtml}
                            <button class="btn btn-outline-light fav-btn-favorite mt-2" data-product-id="${product.product_id}">
                                <i class="fas fa-heart"></i> Убрать из избранного
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }



    function favAddToCart(product) {
        const userId = favGetUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        $.ajax({
            url: BASE_URL + 'cart',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ user_id: userId, product_id: product.id, quantity: product.quantity || 1 }),
            dataType: 'json',
            success: function(response) {
                if(response.status == 200 || response.status == 201) {
                    favFetchCart(userId, favLoadFavorites);
                    syncCartWithUI();

                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Ошибка', 'Не удалось добавить товар в корзину', 'error');
            }
        });
    }

    function favUpdateCartQuantity(cartItemId, quantity) {
        const userId = favGetUserId();
        $.ajax({
            url: BASE_URL + 'cart/' + cartItemId,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ quantity: quantity }),
            dataType: 'json',
            success: function(response) {
                if(response.status === 200) {
                    favFetchCart(userId, function(){
                        favLoadFavorites();
                        // Добавляем обновление счетчика в шапке
                        updateCartCount();
                        fetchCart(userId)
                    });
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Ошибка', 'Не удалось обновить количество товара', 'error');
            }
        });
    }

    function favRemoveFromCart(productId) {
        const cartItem = favCart.find(item => parseInt(item.product_id) === productId);
        if(cartItem) {
            favUpdateCartQuantity(cartItem.id, 0);
        }
    }

    function favRemoveFromFavorites(productId) {
        const userId = favGetUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        const favRecord = favFavorites.find(item => parseInt(item.product_id) === productId);
        if (!favRecord) {
            Swal.fire('Ошибка', 'Товар не найден в избранном', 'error');
            return;
        }
        $.ajax({
            url: BASE_URL + 'favorite-products/' + favRecord.id,
            method: 'DELETE',
            dataType: 'json',
            success: function(response) {
                if(response.status == 200) {
                    Swal.fire({ icon: "info", title: "Удалено из избранного", timer: 1500, showConfirmButton: false });
                    favFetchFavorites(function(){
                        favLoadFavorites();
                        updateFavoriteUI();
                    });
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', `Не удалось удалить товар из избранного: ${xhr.status} - ${error}`, 'error');
            }
        });
    }
    function updateFavoriteUI() {
        $('.btn-favorite-toggle').each(function(){
            const productId = parseInt($(this).data('product-id'));
            const exists = favFavorites.some(item => parseInt(item.product_id) === productId);
            if (exists) {
                $(this).find('i').removeClass('far').addClass('fas');
            } else {
                $(this).find('i').removeClass('fas').addClass('far');
            }
        });
    }



    function initFavorites() {
        const userId = favGetUserId();
        if (userId) {
            favFetchCart(userId, function(){
                favFetchFavorites(function(){
                    favLoadFavorites();
                    updateFavoriteUI();
                });
            });
        } else {
            $('#fav-favorites-container').html('<p>Пользователь не авторизован</p>');
        }
        // Делегирование событий для элементов избранного
        $('#fav-favorites-container')
            .off('click')
            .on('click', '.add-to-cart-btn', function(){
                const productId = parseInt($(this).data('product-id'));
                favAddToCart({ id: productId });
                fetchCart(userId);
            })
            .on('click', '.fav-quantity-increase', function(){
                const cartId = $(this).data('fav-cart-id');
                const qtyElem = $(`.fav-quantity-value[data-fav-cart-id="${cartId}"]`);
                let qty = parseInt(qtyElem.text(), 10);
                qty++;
                qtyElem.text(qty);
                favUpdateCartQuantity(cartId, qty);

            })
            .on('click', '.fav-quantity-decrease', function(){
                const cartId = $(this).data('fav-cart-id');
                const qtyElem = $(`.fav-quantity-value[data-fav-cart-id="${cartId}"]`);
                let qty = parseInt(qtyElem.text(), 10);
                if(qty > 1) {
                    qty--;
                    qtyElem.text(qty);
                    favUpdateCartQuantity(cartId, qty);
                } else {
                    favRemoveFromCart(parseInt($(this).data('product-id')));
                }
            })
            .on('click', '.fav-btn-favorite', function(){
                const productId = parseInt($(this).data('product-id'));
                favRemoveFromFavorites(productId);
            });
    }
</script>
<script src="js/new-products.js"></script>
</body>
</html>

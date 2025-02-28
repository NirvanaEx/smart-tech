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
            <button class="btn btn-outline-light" data-page="favorites" data-title="Избранное">
                <i class="fas fa-heart"></i> Избранное
            </button>
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
    <!-- Default container: баннер + новинки -->
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
                <div class="text-center text-light">Загрузка...</div>
            </div>
        </section>
    </div>

    <!-- Dynamic container: для фильтров (all.php, поиск, категория и т.д.) -->
    <div id="dynamic-content-container" style="display: none;">
        <!-- Убрали динамический заголовок, чтобы не дублировать текст -->
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

    // Функция для загрузки динамического контента (например, all.php)
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
                // Добавляем вызов для синхронизации корзины и избранного
                const userId = getUserId();
                if (userId) {
                    fetchCart(userId);
                    fetchFavorites();
                }
            })
            .catch(error => {
                contentArea.innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
            });
    }

    // Функция для загрузки новых товаров (new.php) в default контейнер
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
                        // Не сбрасываем поиск — если пользователь уже что-то ввёл, сохраняем
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

        // Обработчик изменения в поле поиска
        // УБРАЛИ сброс категории, чтобы поиск мог работать вместе с категорией
        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("input", function () {
            currentSearch = searchInput.value.trim();
            loadDynamicContent();
        });

        // Обработчик клика по кнопке "Shop Now" – переключение на dynamic контент
        const shopNowButton = document.getElementById("shopNowButton");
        shopNowButton.addEventListener("click", function (e) {
            e.preventDefault();
            // Если нужно, сбрасываем поиск при нажатии "Shop Now"
            currentSearch = '';
            loadDynamicContent();
        });

        // Обработка кликов для элементов с data-page (сброс фильтров)
        document.addEventListener('click', function (event) {
            const target = event.target.closest('[data-page]');
            if (target) {
                event.preventDefault();
                currentCategory = '';
                currentSubcategory = '';
                currentSearch = '';
                document.getElementById("default-container").style.display = "none";
                document.getElementById("dynamic-content-container").style.display = "block";
                const page = target.getAttribute('data-page');
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
    });
</script>
</body>
</html>

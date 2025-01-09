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

        /* Swiper styles */
        .swiper {
            width: 100%;
            max-width: 1200px; /* Ограничение ширины */
            margin: 0 auto; /* Центрирование */
        }

        .swiper-wrapper {
            display: flex;
        }

        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0; /* Предотвращаем сжатие слайдов */
            width: auto; /* Автоматическая ширина */
        }

        .swiper-slide img {
            width: 100%;
            max-height: 300px; /* Ограничение высоты изображений */
            object-fit: cover; /* Пропорциональное заполнение */
            border-radius: 10px;
        }

        .swiper-button-prev,
        .swiper-button-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: auto;
            height: auto;
            z-index: 10;
            color: #fff;
        }

        .swiper-button-prev {
            left: -30px; /* Сместить левую стрелку */
        }

        .swiper-button-next {
            right: -30px; /* Сместить правую стрелку */
        }

        .swiper-pagination {
            position: relative;
            margin-top: 10px;
        }

        .swiper-pagination-bullet {
            background: #d3d3d3; /* Светло-серый цвет для точек */
            opacity: 0.8;
        }

        .swiper-pagination-bullet-active {
            background: #fff; /* Белый цвет для активного круга */
            opacity: 1;
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

        /* Каталог */
        /* Установка контейнера для кнопки и выпадающего списка */
        .dropdown-catalog {
            position: relative;
            display: inline-block;
        }

        /* Настройка контейнера Select2 */
        .select2-container {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            width: 300px !important; /* Фиксированная ширина */
            margin-top: 5px; /* Отступ от кнопки */
            display: none; /* По умолчанию скрыт */
        }

        /* Показать Select2 */
        .select2-container--open {
            display: block !important;
        }

        /* Общий стиль выпадающего списка */
        .select2-container .select2-dropdown {
            border-radius: 10px; /* Скругленные углы */
            background-color: #242A41; /* Темно-серый фон, как у header */
            border: 1px solid #6c7cdb; /* Синяя рамка для выделения */
            color: #ffffff; /* Белый текст */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Легкая тень */
        }

        /* Стили опций */
        .select2-container .select2-results__option {
            padding: 10px; /* Отступы внутри опций */
            background-color: #242A41; /* Темно-серый фон */
            color: #d3d3d3; /* Светло-серый текст для подкатегорий */
            font-size: 14px; /* Размер шрифта */
            transition: background-color 0.3s; /* Анимация при наведении */
        }

        /* Ховер эффект */
        .select2-container .select2-results__option--highlighted {
            background-color: #6c7cdb; /* Синий фон при наведении */
            color: #ffffff; /* Белый текст */
        }

        /* Группы опций */
        .select2-container .select2-results__group {
            background-color: #242A41; /* Темно-серый фон */
            color: #ffffff; /* Белый текст для групп */
            font-weight: bold;
            padding: 8px;
        }

        /* Поле поиска */
        .select2-container .select2-search--dropdown {
            padding: 10px;
            border-bottom: 1px solid #6c7cdb;
            background-color: #ffffff; /* Белый фон для поиска */
        }

        .select2-container .select2-search__field {
            width: 100%;
            padding: 8px;
            border-radius: 5px; /* Скругленные углы */
            background-color: #ffffff; /* Белый фон */
            color: #000000; /* Черный текст */
            border: 1px solid #6c7cdb; /* Синяя рамка */
        }

        /* Стилизация скроллбара */
        .select2-container .select2-results__options {
            max-height: 200px; /* Ограничение высоты списка */
            overflow-y: auto; /* Скроллинг */
            scrollbar-width: thin; /* Тонкий скроллбар (для Firefox) */
            scrollbar-color: #ffffff #242A41; /* Белый скроллбар */
        }

        .select2-container .select2-results__options::-webkit-scrollbar {
            width: 8px;
        }

        .select2-container .select2-results__options::-webkit-scrollbar-thumb {
            background: #ffffff; /* Белая полоска скроллбара */
            border-radius: 5px; /* Скругление */
        }

        .select2-container .select2-results__options::-webkit-scrollbar-track {
            background: #242A41; /* Темно-серый фон скроллбара */
        }

        /* Позиционирование выпадающего списка */
        .select2-container--open {
            border-radius: 10px; /* Скругление углов */
            border: 1px solid #6c7cdb; /* Синяя рамка */
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

        .btn-favorite.active {
            color: red; /* Красный цвет для активного сердечка */
        }

        #cart-count {
            display: none; /* Скрываем метку по умолчанию */
            font-size: 0.75rem;
            padding: 0.5em;
            transform: translate(-50%, -50%);
        }


    </style>
</head>
<body class="bg-dark text-light">
<!-- Header -->
<header class="bg-secondary py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="logo d-flex align-items-center">
            <a href="#" class="d-flex align-items-center logo-link">
                <img src="upload/logo-placeholder.png" alt="Logo" style="height: 40px;" class="me-3">
                <span>SMART.INC</span>
            </a>
        </div>

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

            <button class="btn btn-outline-light position-relative">
                <i class="fas fa-shopping-cart"></i> Корзина
                <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </button>
            <input type="text" class="form-control" placeholder="Напишите название товара...">
            <button class="btn btn-outline-light"><i class="fas fa-heart"></i> Избранное</button>
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
            <a href="#" class="btn">Shop Now</a>
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


    document.addEventListener("DOMContentLoaded", function () {
        const shopNowButton = document.querySelector('.banner-text .btn');
        const mainContainer = document.getElementById("main-container");

        shopNowButton.addEventListener('click', function (event) {
            event.preventDefault(); // Останавливаем переход по ссылке

            // Показываем индикатор загрузки
            mainContainer.innerHTML = '<div class="text-center text-light">Загрузка...</div>';

            // Загружаем контент через AJAX
            fetch('pages/all.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка загрузки страницы');
                    }
                    return response.text();
                })
                .then(html => {
                    // Заменяем содержимое main-container
                    mainContainer.innerHTML = html;

                    // Повторно инициализируем обработчики событий
                    initializeFavoriteButtons();
                    initializeCartButtons();
                })
                .catch(error => {
                    // Обработка ошибок
                    mainContainer.innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
                });
        });

        // Функция для инициализации обработчиков событий для "Избранное"
        function initializeFavoriteButtons() {
            console.log('Инициализация кнопок избранного');

            // Добавляем обработчики для кнопок "Избранное"
            document.querySelectorAll('.btn-favorite').forEach(button => {
                button.addEventListener('click', function () {
                    console.log('Кнопка избранного нажата:', this); // Логируем нажатие
                    this.classList.toggle('active'); // Переключаем класс active
                });
            });
        }

        // Функция для инициализации обработчиков событий для "В корзинку"
        function initializeCartButtons() {
            console.log('Инициализация кнопок корзинки');

            // Находим все кнопки "В корзинку"
            document.querySelectorAll('.btn-cart').forEach(button => {
                button.addEventListener('click', function () {
                    // Проверяем, если кнопка уже активна
                    if (this.classList.contains('active')) {
                        return;
                    }

                    // Делаем кнопку активной
                    this.classList.add('active');
                    this.innerHTML = `
                        <div class="cart-container d-flex align-items-center justify-content-between w-100">
                            <button class="btn btn-light btn-decrease border-white">-</button>
                            <span class="quantity text-white border border-white">1</span>
                            <button class="btn btn-light btn-increase border-white">+</button>
                        </div>
                    `;



                    const quantityElement = this.querySelector('.quantity');
                    const decreaseButton = this.querySelector('.btn-decrease');
                    const increaseButton = this.querySelector('.btn-increase');

                    // Обработка кнопки увеличения количества
                    increaseButton.addEventListener('click', function (event) {
                        event.stopPropagation();
                        let quantity = parseInt(quantityElement.textContent, 10);
                        quantityElement.textContent = ++quantity;
                    });

                    // Обработка кнопки уменьшения количества
                    decreaseButton.addEventListener('click', function (event) {
                        event.stopPropagation();
                        let quantity = parseInt(quantityElement.textContent, 10);
                        if (quantity > 1) {
                            quantityElement.textContent = --quantity;
                        } else {
                            // Возврат к исходной кнопке, если количество равно 1
                            button.classList.remove('active');
                            button.innerHTML = `<i class="fas fa-cart-plus"></i> В корзинку`;
                        }
                    });
                });
            });
        }

        // Инициализируем события для текущей страницы (если есть кнопки)
        initializeFavoriteButtons();
        initializeCartButtons();
    });



    $(document).ready(function () {
        // Инициализация Select2
        $('#categories').select2({
            placeholder: "Выберите категорию",
            allowClear: true,
            dropdownParent: $('.dropdown-catalog') // Привязка к контейнеру
        });

        // Флаг для управления состоянием
        let isOpen = false;

        // Управление видимостью списка при нажатии на кнопку "Каталог"
        $('#catalogButton').on('click', function () {
            if (isOpen) {
                $('#categories').select2('close'); // Закрыть, если открыт
                isOpen = false;
            } else {
                $('#categories').select2('open'); // Открыть, если закрыт
                isOpen = true;
            }
        });

        // Сброс флага при закрытии через Select2
        $('#categories').on('select2:close', function () {
            isOpen = false;
        });

        // Обработка выбора категории
        $('#categories').on('select2:select', function (e) {
            const selectedCategory = e.params.data.text;
            alert(`Вы выбрали: ${selectedCategory}`);
            // Логика для отображения подкатегорий или загрузки контента
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const loginButton = document.querySelector('.fa-sign-in-alt').closest('button');

        loginButton.addEventListener('click', function () {
            // Окно входа
            Swal.fire({
                title: 'Вход',
                html: `
                <input type="text" id="login" class="form-control mb-3" placeholder="Логин">
                <input type="password" id="password" class="form-control" placeholder="Пароль">
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary" id="login-submit">Войти</button>
                </div>
                <hr>
                <p class="mt-3 text-center">Нет аккаунта? <a href="#" id="show-register">Зарегистрироваться</a></p>
            `,
                showConfirmButton: false,
                showCloseButton: true,
            });

            // Обработчик кнопки регистрации
            document.getElementById('show-register').addEventListener('click', function () {
                Swal.fire({
                    title: 'Регистрация',
                    html: `
                    <input type="text" id="register-login" class="form-control mb-3" placeholder="Логин">
                    <input type="email" id="register-email" class="form-control mb-3" placeholder="Email">
                    <input type="password" id="register-password" class="form-control mb-3" placeholder="Пароль">
                    <input type="password" id="register-password-repeat" class="form-control" placeholder="Повторите пароль">
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary" id="register-submit">Зарегистрироваться</button>
                    </div>
                `,
                    showConfirmButton: false,
                    showCloseButton: true,
                });
            });

            // Обработчик кнопки входа
            document.getElementById('login-submit').addEventListener('click', function () {
                const login = document.getElementById('login').value;
                const password = document.getElementById('password').value;

                if (login && password) {
                    // Эмуляция успешного входа
                    Swal.fire({
                        icon: 'success',
                        title: 'Успешный вход',
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        // Замена только кнопки "Войти"
                        loginButton.outerHTML = `
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="account-menu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> Мой аккаунт
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="account-menu">
                                <li><a class="dropdown-item" href="#">Профиль</a></li>
                                <li><a class="dropdown-item" href="#">Настройки</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="logout">Выйти</a></li>
                            </ul>
                        </div>
                    `;

                        // Обработчик кнопки выхода
                        document.getElementById('logout').addEventListener('click', function () {
                            Swal.fire({
                                icon: 'info',
                                title: 'Вы вышли из аккаунта',
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                location.reload(); // Перезагрузка страницы
                            });
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: 'Введите логин и пароль!',
                    });
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const mainContainer = document.getElementById("main-container");
        const contentArea = document.getElementById("dynamic-content-area");

        // Загружаем скрипты/стили для страницы
        function loadResources(page) {
            // Инициализация Swiper при подгрузке страницы new
            if (page === 'new') {
                const swiper = new Swiper('.swiper', {
                    loop: true,
                    slidesPerView: 1,
                    spaceBetween: 10,
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                        dynamicBullets: true,
                    },
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    breakpoints: {
                        640: { slidesPerView: 2, spaceBetween: 10 },
                        768: { slidesPerView: 3, spaceBetween: 10 },
                        1024: { slidesPerView: 4, spaceBetween: 10 },
                    }
                });
            }
        }

        // Загрузка контента через AJAX
        function loadContent(page, title) {
            contentArea.innerHTML = '<div class="text-center text-light">Загрузка...</div>';
            fetch(`pages/${page}.php`)
                .then(response => {
                    if (!response.ok) throw new Error("Ошибка загрузки страницы");
                    return response.text();
                })
                .then(html => {
                    contentArea.innerHTML = html;
                    document.getElementById("dynamic-content-title").textContent = title;

                    // Подключаем ресурсы
                    loadResources(page);
                })
                .catch(error => {
                    contentArea.innerHTML = `<div class="text-danger text-center">Ошибка: ${error.message}</div>`;
                });
        }

        // Обработка кликов на кнопки
        document.querySelectorAll('[data-page]').forEach(button => {
            button.addEventListener('click', function () {
                const page = this.getAttribute('data-page');
                const title = this.getAttribute('data-title');
                loadContent(page, title);
            });
        });

        // Загрузка страницы по умолчанию
        loadContent('new', 'Новинки');
    });
</script>
</body>
</html>

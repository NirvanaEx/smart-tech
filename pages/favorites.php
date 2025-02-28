<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Избранные товары</title>
    <style>
        .cart-controls-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .cart-controls {
            flex: 1;
        }
        .cart-controls-container .btn {
            text-align: center;
        }
        .btn-favorite,
        .btn-compare {
            flex: initial;
            margin-left: 5px;
        }
    </style>
    <!-- Подключаем jQuery и SweetAlert -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container my-4">
    <div class="row g-4" id="favorites-container">
        <!-- Избранные товары будут отображаться здесь -->
    </div>
</div>

<script>


    // Функция для получения ID пользователя (пример: из localStorage)
    function getUserId() {
        const user = JSON.parse(localStorage.getItem('user'));
        return user ? user.user_id : null;
    }

    // Функция для получения корзины с сервера
    function fetchCart(userId) {
        $.ajax({
            url: `${BASE_URL}cart/${userId}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status == 200) {
                    cart = response.data;
                } else {
                    cart = [];
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка получения корзины:', error);
                cart = [];
            }
        });
    }

    // Функция для получения избранных товаров для текущего пользователя
    function fetchFavorites(callback) {
        const userId = getUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        $.ajax({
            url: `${BASE_URL}favorite-products/${userId}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status != 200) {
                    Swal.fire('Ошибка', response.message, 'error');
                    return;
                }
                favorites = response.data; // Обновляем глобальное хранилище избранного
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', `Не удалось получить избранные товары: ${xhr.status} - ${error}`, 'error');
            }
        });
    }

    // Функция для загрузки избранных товаров и их отображения на странице
    function loadFavorites() {
        const favoritesContainer = $('#favorites-container');
        favoritesContainer.html('<div class="text-center text-light">Загрузка...</div>');

        // Получаем массив id товаров, добавленных в избранное (из поля product_id)
        const favoritesIds = favorites.map(item => parseInt(item.product_id));

        $.ajax({
            url: `${BASE_URL}products`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status !== 200) {
                    favoritesContainer.html(`<div class="text-danger text-center">Ошибка: ${response.message}</div>`);
                    return;
                }

                const products = response.data;
                // Отбираем только те товары, id которых есть в favoritesIds
                const favoriteProducts = products.filter(product => favoritesIds.includes(product.id));

                favoritesContainer.empty();

                if (favoriteProducts.length === 0) {
                    favoritesContainer.html('<div class="text-center text-light">Нет избранных товаров.</div>');
                    return;
                }

                favoriteProducts.forEach(product => {
                    // Проверяем, добавлен ли товар в корзину (сравниваем по product.id)
                    const cartItem = cart.find(item => item.product_id === product.id);
                    const isInCart = !!cartItem;

                    const card = `
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                            <div class="card bg-dark text-light h-100">
                                <img src="${product.image_url}" class="card-img-top" alt="${product.product_name}">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">${product.product_name}</h5>
                                    <p class="card-text">${product.description}</p>
                                    <p class="card-text"><strong>Цена:</strong> ${product.price} ₽</p>
                                    <div class="mt-auto d-flex align-items-center cart-controls-container">
                                        <div class="cart-controls" data-product-id="${product.id}">
                                            ${isInCart
                        ? `
                                                    <div class="quantity-controls d-flex align-items-center">
                                                        <button class="btn btn-outline-light quantity-decrease" data-product-id="${product.id}">-</button>
                                                        <span class="quantity-value mx-2" data-product-id="${product.id}">${cartItem.quantity}</span>
                                                        <button class="btn btn-outline-light quantity-increase" data-product-id="${product.id}">+</button>
                                                    </div>
                                                `
                        : `
                                                    <button class="btn btn-outline-light add-to-cart-btn w-100" data-product-id="${product.id}">
                                                        <i class="fas fa-shopping-bag"></i> В корзину
                                                    </button>
                                                `}
                                        </div>
                                        <div class="d-flex">
                                            <button class="btn btn-outline-light btn-favorite active" data-product-id="${product.id}" style="color: red;">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <button class="btn btn-outline-light btn-compare" data-product-id="${product.id}">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    favoritesContainer.append(card);
                });
            },
            error: function(xhr, status, error) {
                favoritesContainer.html(`<div class="text-danger text-center">Ошибка: ${xhr.status} - ${error}</div>`);
            }
        });
    }

    // Функция для добавления товара в корзину
    function addToCart(product) {
        const userId = getUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        $.ajax({
            url: `${BASE_URL}cart`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                user_id: userId,
                product_id: product.id,
                quantity: product.quantity ? product.quantity : 1
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status == 200 || response.status == 201) {
                    // Обновляем корзину после добавления
                    fetchCart(userId);
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', 'Не удалось добавить товар в корзину', 'error');
            }
        });
    }

    // Функция для удаления товара из корзины (сбрасываем количество до 0)
    function removeFromCart(productId) {
        const userId = getUserId();
        $.ajax({
            url: `${BASE_URL}cart/${productId}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ quantity: 0 }),
            dataType: 'json',
            success: function(response) {
                if (response.status == 200) {
                    fetchCart(userId);
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', 'Не удалось удалить товар из корзины', 'error');
            }
        });
    }

    // Функция для удаления товара из избранного
    function removeFromFavorites(productId) {
        const userId = getUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        // Находим запись избранного по productId (поле product_id)
        const favoriteRecord = favorites.find(item => parseInt(item.product_id) === productId);
        if (!favoriteRecord) {
            Swal.fire('Ошибка', 'Товар не найден в избранном', 'error');
            return;
        }
        $.ajax({
            url: `${BASE_URL}favorite-products/${favoriteRecord.id}`,
            method: 'DELETE',
            dataType: 'json',
            success: function(response) {
                if (response.status == 200) {
                    Swal.fire({
                        icon: "info",
                        title: "Удалено из избранного",
                        text: "Товар удалён из избранного.",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // После удаления обновляем список избранного и перерисовываем страницу
                    fetchFavorites(loadFavorites);
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', `Не удалось удалить товар из избранного: ${xhr.status} - ${error}`, 'error');
            }
        });
    }

    $(document).ready(function () {
        // При загрузке страницы получаем ID пользователя, корзину и избранное
        const userId = getUserId();
        if (userId) {
            fetchCart(userId);
            fetchFavorites(loadFavorites);
        }

        // Обработчик для кнопок "Добавить в корзину"
        $('#favorites-container').on('click', '.add-to-cart-btn', function () {
            const productId = parseInt($(this).data('product-id'));
            const productName = $(this).closest('.card-body').find('.card-title').text().trim();
            const productPrice = parseFloat($(this).closest('.card-body').find('.card-text strong').text().replace(/[^\d.]/g, ''));
            addToCart({
                id: productId,
                name: productName,
                price: productPrice
            });
            Swal.fire({
                icon: "success",
                title: "Товар добавлен в корзину!",
                text: `${productName} успешно добавлен.`,
                timer: 1500,
                showConfirmButton: false
            });
            // Обновляем избранное после добавления в корзину
            fetchFavorites(loadFavorites);
        });

        // Обработчик для кнопок "+" (увеличение количества товара)
        $('#favorites-container').on('click', '.quantity-increase', function () {
            const productId = parseInt($(this).data('product-id'));
            const quantityValueElement = $(`.quantity-value[data-product-id="${productId}"]`);
            let quantity = parseInt(quantityValueElement.text(), 10);
            quantity++;
            quantityValueElement.text(quantity);
            // Добавляем в корзину ещё один товар
            addToCart({
                id: productId,
                quantity: 1
            });
        });

        // Обработчик для кнопок "-" (уменьшение количества товара)
        $('#favorites-container').on('click', '.quantity-decrease', function () {
            const productId = parseInt($(this).data('product-id'));
            const quantityValueElement = $(`.quantity-value[data-product-id="${productId}"]`);
            let quantity = parseInt(quantityValueElement.text(), 10);
            if (quantity > 1) {
                quantity--;
                quantityValueElement.text(quantity);
                removeFromCart(productId);
            } else if (quantity === 1) {
                removeFromCart(productId);
                fetchFavorites(loadFavorites);
            }
        });

        // Обработчик для кнопок удаления из избранного
        $('#favorites-container').on('click', '.btn-favorite', function () {
            const productId = parseInt($(this).data('product-id'));
            removeFromFavorites(productId);
        });
    });
</script>
</body>
</html>

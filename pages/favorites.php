<?php
// favorites.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранные товары - SMART.INC</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Дополнительные стили для избранного -->
    <style>
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
</head>
<body class="bg-dark text-light">
<div class="fav-container my-4">
    <h1>Избранные товары</h1>
    <div id="fav-favorites-container"></div>
</div>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Определение BASE_URL (используйте тот же адрес, что и в index.php) -->
<script>
    const BASE_URL = 'http://smart-tech/API/';
</script>
<!-- Скрипты для работы с избранным и синхронизации с корзиной -->
<script>
    // Префикс "fav-" используется для функций избранного
    let favCart = [];
    let favFavorites = [];

    // Функция получения ID пользователя из localStorage
    function favGetUserId() {
        const user = JSON.parse(localStorage.getItem('user'));
        return user ? user.user_id : null;
    }

    // Получаем корзину пользователя
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

    // Получаем список избранных товаров пользователя
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

    // Функция отрисовки карточек избранных товаров
    function favLoadFavorites() {
        const container = $('#fav-favorites-container');
        container.empty();
        if (!favFavorites.length) {
            container.html('<p>Нет избранных товаров.</p>');
            return;
        }
        // Формируем массив id избранных товаров
        const favIds = favFavorites.map(item => parseInt(item.product_id));
        $.ajax({
            url: BASE_URL + 'products',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.status !== 200) {
                    container.html(`<p>Ошибка: ${response.message}</p>`);
                    return;
                }
                const products = response.data;
                const favoriteProducts = products.filter(product => favIds.includes(product.id));
                if(!favoriteProducts.length) {
                    container.html('<p>Нет избранных товаров.</p>');
                    return;
                }
                favoriteProducts.forEach(product => {
                    // Если товар уже есть в корзине, выводим элементы управления количеством
                    const cartItem = favCart.find(item => parseInt(item.product_id) === product.id);
                    let controlsHtml = '';
                    if(cartItem) {
                        controlsHtml = `
                          <div class="fav-quantity-controls">
                              <button class="fav-btn fav-quantity-decrease" data-product-id="${product.id}" data-fav-cart-id="${cartItem.id}">-</button>
                              <span class="fav-quantity-value" data-product-id="${product.id}" data-fav-cart-id="${cartItem.id}">${cartItem.quantity}</span>
                              <button class="fav-btn fav-quantity-increase" data-product-id="${product.id}" data-fav-cart-id="${cartItem.id}">+</button>
                          </div>
                          `;
                    } else {
                        controlsHtml = `<button class="fav-btn fav-btn-add add-to-cart-btn" data-product-id="${product.id}">В корзину</button>`;
                    }
                    const cardHtml = `
                      <div class="fav-card" data-product-id="${product.id}">
                          <img src="${product.image_path}" alt="${product.product_name}">
                          <h3>${product.product_name}</h3>
                          <p>${product.description}</p>
                          <p><strong>Цена:</strong> ${product.price} ₽</p>
                          <div class="fav-cart-controls-container">
                              <div class="fav-cart-controls" data-product-id="${product.id}">
                                  ${controlsHtml}
                              </div>
                          </div>
                          <button class="fav-btn fav-btn-favorite" data-product-id="${product.id}">Удалить из избранного</button>
                      </div>
                      `;
                    container.append(cardHtml);
                });
            },
            error: function(xhr, status, error) {
                container.html(`<p>Ошибка: ${xhr.status} - ${error}</p>`);
            }
        });
    }

    // Функция добавления товара в корзину
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
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Ошибка', 'Не удалось добавить товар в корзину', 'error');
            }
        });
    }

    // Функция обновления количества товара в корзине
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
                    favFetchCart(userId, favLoadFavorites);
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Ошибка', 'Не удалось обновить количество товара', 'error');
            }
        });
    }

    // Функция удаления товара из корзины (если нужно уменьшить до 0)
    function favRemoveFromCart(productId) {
        const cartItem = favCart.find(item => parseInt(item.product_id) === productId);
        if(cartItem) {
            favUpdateCartQuantity(cartItem.id, 0);
        }
    }

    // Функция удаления товара из избранного
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
                    favFetchFavorites(favLoadFavorites);
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Ошибка', `Не удалось удалить товар из избранного: ${xhr.status} - ${error}`, 'error');
            }
        });
    }

    // Инициализация событий при загрузке документа
    $(document).ready(function(){
        const userId = favGetUserId();
        if(userId){
            favFetchCart(userId, function(){
                favFetchFavorites(favLoadFavorites);
            });
        } else {
            $('#fav-favorites-container').html('<p>Пользователь не авторизован</p>');
        }

        // Добавление товара в корзину
        $('#fav-favorites-container').on('click', '.add-to-cart-btn', function(){
            const productId = parseInt($(this).data('product-id'));
            favAddToCart({ id: productId });
        });

        // Увеличение количества товара в корзине
        $('#fav-favorites-container').on('click', '.fav-quantity-increase', function(){
            const cartId = $(this).data('fav-cart-id');
            const qtyElem = $(`.fav-quantity-value[data-fav-cart-id="${cartId}"]`);
            let qty = parseInt(qtyElem.text(), 10);
            qty++;
            qtyElem.text(qty);
            favUpdateCartQuantity(cartId, qty);
        });

        // Уменьшение количества товара в корзине (при достижении 1 – удаляем товар)
        $('#fav-favorites-container').on('click', '.fav-quantity-decrease', function(){
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
        });

        // Удаление товара из избранного
        $('#fav-favorites-container').on('click', '.fav-btn-favorite', function(){
            const productId = parseInt($(this).data('product-id'));
            favRemoveFromFavorites(productId);
        });
    });
</script>
</body>
</html>

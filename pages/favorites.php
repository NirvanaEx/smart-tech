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
<div class="container my-4">
    <div class="row g-4" id="favorites-container">
        <!-- Избранные товары будут отображаться здесь -->
    </div>
</div>

<script>
    $(document).ready(function () {
        const favoritesIds = favorites.map(item => item.id); // Список ID избранных товаров

        // Функция для загрузки избранных товаров
        function loadFavorites() {
            const favoritesContainer = $('#favorites-container');
            favoritesContainer.html('<div class="text-center text-light">Загрузка...</div>');

            $.ajax({
                url: 'http://smart-tech/API/products',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status !== 200) {
                        favoritesContainer.html(`<div class="text-danger text-center">Ошибка: ${response.message}</div>`);
                        return;
                    }

                    const products = response.data;
                    const favoriteProducts = products.filter(product => favoritesIds.includes(product.id));

                    favoritesContainer.empty();

                    if (favoriteProducts.length === 0) {
                        favoritesContainer.html('<div class="text-center text-light">Нет избранных товаров.</div>');
                        return;
                    }

                    favoriteProducts.forEach(product => {
                        // Проверяем, есть ли товар в корзине
                        const cartItem = cart.find(item => item.id === product.id);
                        const isInCart = !!cartItem; // Преобразуем в булево значение

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
                    `;
                        favoritesContainer.append(card);
                    });
                },
                error: function (xhr, status, error) {
                    favoritesContainer.html(`<div class="text-danger text-center">Ошибка: ${xhr.status} - ${error}</div>`);
                }
            });
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

            loadFavorites(); // Обновляем избранное
        });

        // Обработчик для кнопок "+" (увеличение количества)
        $('#favorites-container').on('click', '.quantity-increase', function () {
            const productId = parseInt($(this).data('product-id'));
            const quantityValueElement = $(`.quantity-value[data-product-id="${productId}"]`);
            let quantity = parseInt(quantityValueElement.text(), 10);

            quantity++;
            quantityValueElement.text(quantity);

            addToCart({
                id: productId
            });
        });

        // Обработчик для кнопок "-" (уменьшение количества)
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
                loadFavorites(); // Обновляем избранное
            }
        });

        // Удаление товара из избранного
        $('#favorites-container').on('click', '.btn-favorite', function () {
            const productId = parseInt($(this).data('product-id'));
            removeFromFavorites(productId);
            Swal.fire({
                icon: "info",
                title: "Удалено из избранного",
                text: `Товар удалён из избранного.`,
                timer: 1500,
                showConfirmButton: false
            });
            loadFavorites();
        });

        // Загружаем избранные товары при открытии страницы
        loadFavorites();
    });
</script>

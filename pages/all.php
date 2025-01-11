<style>
    .cart-controls-container {
        display: flex;
        justify-content: space-between; /* Располагаем кнопки по краям */
        align-items: center; /* Центрируем кнопки по вертикали */
        gap: 10px; /* Расстояние между группами кнопок */
    }

    .cart-controls {
        flex: 1; /* Кнопка "В корзину" займет оставшееся пространство */
    }

    .cart-controls-container .btn {
        text-align: center; /* Центрируем текст внутри кнопок */
    }

    .btn-favorite,
    .btn-compare {
        flex: initial; /* Убираем растяжение для этих кнопок */
        margin-left: 5px; /* Добавляем небольшой отступ между ними */
    }
</style>
<div class="container my-4">
    <div class="row g-4" id="products-container">
        <!-- Данные будут загружаться динамически через AJAX -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script>
    function markFavorites() {
        $('#products-container .btn-favorite').each(function () {
            const productId = parseInt($(this).data('product-id'));
            if (isFavorite(productId)) {
                $(this).addClass('active').css('color', 'red'); // Отмечаем как избранное
            } else {
                $(this).removeClass('active').css('color', ''); // Сбрасываем состояние
            }
        });
    }

    $(document).ready(function () {
        const productStock = {}; // Хранение доступного количества товара по ID

        // Функция для загрузки данных через AJAX
        function loadProducts() {
            const productsContainer = $('#products-container');
            productsContainer.html('<div class="text-center text-light">Загрузка...</div>');

            $.ajax({
                url: 'http://smart-tech/API/products',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status !== 200) {
                        productsContainer.html(`<div class="text-danger text-center">Ошибка: ${response.message}</div>`);
                        return;
                    }

                    const products = response.data;
                    productsContainer.empty(); // Очищаем контейнер

                    products.forEach(product => {
                        const card = `
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card bg-dark text-light h-100">
                        <img src="${product.image_url}" class="card-img-top" alt="${product.product_name}">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${product.product_name}</h5>
                            <p class="card-text">${product.description}</p>
                            <p class="card-text"><strong>Цена:</strong> ${product.price} ₽</p>
                           <div class="mt-auto d-flex align-items-center cart-controls-container">
                            <div class="cart-controls" data-product-id="${product.id}">
                                <button class="btn btn-outline-light add-to-cart-btn w-100" data-product-id="${product.id}">
                                    <i class="fas fa-shopping-bag"></i> В корзину
                                </button>
                            </div>
                            <div class="d-flex">
                                <button class="btn btn-outline-light btn-favorite" data-product-id="${product.id}">
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
                        productsContainer.append(card);
                    });

                    // Отмечаем избранные товары
                    markFavorites();
                },
                error: function (xhr, status, error) {
                    productsContainer.html(`<div class="text-danger text-center">Ошибка: ${xhr.status} - ${error}</div>`);
                }
            });
        }

        $('#products-container').on('click', '.btn-favorite', function (e) {
            const button = $(this);
            const productId = parseInt(button.data('product-id'));
            const productName = button.closest('.card-body').find('.card-title').text().trim();
            const productPrice = parseFloat(button.closest('.card-body').find('.card-text strong').text().replace(/[^\d.]/g, ''));

            if (button.hasClass('active')) {
                // Если товар уже в избранном, удаляем его
                removeFromFavorites(productId);
                Swal.fire({
                    icon: "info",
                    title: "Удалено из избранного",
                    text: `${productName} удалён из избранного.`,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                // Если товара нет в избранном, добавляем его
                addToFavorites({
                    id: productId,
                    name: productName,
                    price: productPrice
                });
                Swal.fire({
                    icon: "success",
                    title: "Добавлено в избранное",
                    text: `${productName} добавлен в избранное.`,
                    timer: 1500,
                    showConfirmButton: false
                });
            }

            // Переключение класса "active" для кнопки
            button.toggleClass('active');
            button.css('color', button.hasClass('active') ? 'red' : '');
        });

        // Делегирование событий для кнопок "+" (увеличение количества)
        $('#products-container').on('click', '.quantity-increase', function () {
            const productId = parseInt($(this).data('product-id'));
            const quantityValueElement = $(`.quantity-value[data-product-id="${productId}"]`);
            let quantity = parseInt(quantityValueElement.text(), 10);

            if (quantity < productStock[productId]) {
                quantity++;
                quantityValueElement.text(quantity);

                // Увеличиваем количество через addToCart
                addToCart({
                    id: productId
                });
            } else {
                // Если превышает доступное количество
                Swal.fire({
                    icon: "warning",
                    title: "Лимит товара",
                    text: `Извините, доступно только ${productStock[productId]} шт.`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        // Делегирование событий для кнопок "-" (уменьшение количества)
        $('#products-container').on('click', '.quantity-decrease', function () {
            const productId = parseInt($(this).data('product-id'));
            const quantityValueElement = $(`.quantity-value[data-product-id="${productId}"]`);
            let quantity = parseInt(quantityValueElement.text(), 10);

            if (quantity > 0) {
                quantity--;
                quantityValueElement.text(quantity);

                // Уменьшаем количество через removeFromCart
                removeFromCart(productId);
            }

            if (quantity === 0) {
                const cartControls = $(`.cart-controls[data-product-id="${productId}"]`);
                // Заменяем элементы управления обратно на кнопку
                cartControls.html(`
                    <button class="btn btn-outline-light add-to-cart-btn w-100 me-2" data-product-id="${productId}">
                        <i class="fas fa-shopping-bag"></i> В корзину
                    </button>
                `);
            }
        });

        // Делегирование событий для кнопок "В корзину"
        $('#products-container').on('click', '.add-to-cart-btn', function (e) {
            const productId = parseInt($(this).data('product-id'));
            const productName = $(this).closest('.card-body').find('.card-title').text().trim();
            const productPrice = parseFloat($(this).closest('.card-body').find('.card-text strong').text().replace(/[^\d.]/g, ''));

            // Используем глобальную функцию addToCart
            addToCart({
                id: productId,
                name: productName,
                price: productPrice
            });

            // Отображаем уведомление
            Swal.fire({
                icon: "success",
                title: "Товар добавлен в корзину!",
                text: `${productName} успешно добавлен.`,
                timer: 1500,
                showConfirmButton: false
            });

            // Заменяем кнопку на элементы управления количеством
            const cartControls = $(`.cart-controls[data-product-id="${productId}"]`);
            cartControls.html(`
                <div class="quantity-controls d-flex align-items-center">
                    <button class="btn btn-outline-light quantity-decrease" data-product-id="${productId}">-</button>
                    <span class="quantity-value mx-2" data-product-id="${productId}">1</span>
                    <button class="btn btn-outline-light quantity-increase" data-product-id="${productId}">+</button>
                </div>
            `);
        });

        // Загружаем данные при загрузке страницы
        loadProducts();
    });
</script>



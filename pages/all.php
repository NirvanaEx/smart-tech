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
<?php
// Получаем переданные параметры для фильтрации
$category = isset($_GET['category']) ? $_GET['category'] : '';
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>
<!-- Хлебные крошки -->
<div class="mb-3">
    <?php if ($search): ?>
        <h4>Поиск: <?php echo htmlspecialchars($search); ?></h4>
    <?php endif; ?>
    <h4>
        <?php
        if ($category) {
            echo htmlspecialchars($category);
            if ($subcategory) {
                echo " -> " . htmlspecialchars($subcategory);
            }
        } else {
            echo "Все товары";
        }
        ?>
    </h4>
</div>
<div class="container my-4">
    <div class="row g-4" id="products-container">
        <!-- Если товаров нет, будет сообщение -->
    </div>
</div>
<script>
    function fetchFavorites() {
        const userId = getUserId();
        if (!userId) return;
        fetch(`${BASE_URL}favorite-products/${userId}`, { method: 'GET' })
            .then(response => response.json())
            .then(data => {
                if (data.status == 200) {
                    favorites = data.data;
                    markFavorites();
                } else {
                    console.error('Ошибка загрузки избранного:', data.message);
                }
            })
            .catch(error => console.error('Ошибка получения избранного:', error));
    }

    function isFavorite(productId) {
        return favorites.some(fav => parseInt(fav.product_id) === productId);
    }

    function addToFavorites(product) {
        const userId = getUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        fetch(`${BASE_URL}favorite-products`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                product_id: product.id
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status == 200 || data.status == 201) {
                    Swal.fire('Добавлено в избранное', `${product.name} добавлен в избранное.`, 'success');
                    fetchFavorites();
                } else {
                    Swal.fire('Ошибка', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка добавления в избранное:', error);
                Swal.fire('Ошибка', 'Не удалось добавить товар в избранное', 'error');
            });
    }

    function removeFromFavorites(productId) {
        const userId = getUserId();
        if (!userId) {
            Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
            return;
        }
        const favorite = favorites.find(item => parseInt(item.product_id) === productId);
        if (!favorite) {
            Swal.fire('Ошибка', 'Товар не найден в избранном', 'error');
            return;
        }
        fetch(`${BASE_URL}favorite-products/${favorite.id}`, {
            method: 'DELETE'
        })
            .then(response => response.json())
            .then(data => {
                if (data.status == 200) {
                    Swal.fire('Удалено из избранного', 'Товар удалён из избранного', 'success');
                    fetchFavorites();
                } else {
                    Swal.fire('Ошибка', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка удаления избранного:', error);
                Swal.fire('Ошибка', 'Не удалось удалить товар из избранного', 'error');
            });
    }

    function markFavorites() {
        $('#products-container .btn-favorite').each(function () {
            const productId = parseInt($(this).data('product-id'));
            if (isFavorite(productId)) {
                $(this).addClass('active').css('color', 'red');
            } else {
                $(this).removeClass('active').css('color', '');
            }
        });
    }

    $(document).ready(function () {
        const productsContainer = $('#products-container');

        function loadProducts() {
            productsContainer.html('<div class="text-center text-light">Загрузка...</div>');
            $.ajax({
                url: `${BASE_URL}products`,
                method: 'GET',
                dataType: 'json',
                data: {
                    category: '<?php echo htmlspecialchars($category); ?>',
                    subcategory: '<?php echo htmlspecialchars($subcategory); ?>',
                    search: '<?php echo htmlspecialchars($search); ?>'
                },
                success: function (response) {
                    if (response.status !== 200) {
                        productsContainer.html(`<div class="text-danger text-center">Ошибка: ${response.message}</div>`);
                        return;
                    }
                    const products = response.data;
                    productsContainer.empty();
                    if (products.length === 0) {
                        productsContainer.html('<div class="text-center text-light">Нет товаров</div>');
                        return;
                    }
                    products.forEach(product => {
                        const card = `
                            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                <div class="card bg-dark text-light h-100">
                                    <img src="${product.image_url}" class="card-img-top" alt="${product.product_name}">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">${product.product_name}</h5>
                                        <p class="card-text">${product.description}</p>
                                        <p class="card-text"><strong>Цена:</strong> ${product.price} сум</p>
                                        <div class="mt-auto d-flex align-items-center cart-controls-container">
                                            <div class="cart-controls"
                                                 data-product-id="${product.id}"
                                                 data-max-quantity="${product.quantity}">
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
                            </div>`;
                        productsContainer.append(card);
                    });
                    markFavorites();
                    const userId = getUserId();
                    if (userId) {
                        fetchFavorites();
                        fetchCart(userId);
                    }
                },
                error: function (xhr, status, error) {
                    productsContainer.html(`<div class="text-danger text-center">Ошибка: ${xhr.status} - ${error}</div>`);
                }
            });
        }

        productsContainer.on('click', '.btn-favorite', function () {
            const button = $(this);
            const productId = parseInt(button.data('product-id'));
            const productName = button.closest('.card-body').find('.card-title').text().trim();
            if (button.hasClass('active')) {
                removeFromFavorites(productId);
                Swal.fire('Удалено из избранного', `${productName} удалён из избранного.`, 'info');
            } else {
                addToFavorites({ id: productId, name: productName });
                Swal.fire('Добавлено в избранное', `${productName} добавлен в избранное.`, 'success');
            }
            button.toggleClass('active').css('color', button.hasClass('active') ? 'red' : '');
        });

        productsContainer.on('click', '.add-to-cart-btn', function () {
            const button = $(this);
            const productId = parseInt(button.data('product-id'));
            const productName = button.closest('.card-body').find('.card-title').text().trim();
            const productPrice = parseFloat(button.closest('.card-body').find('.card-text strong').text().replace(/[^\d.]/g, ''));
            addToCart({ id: productId, name: productName, price: productPrice });
            Swal.fire('Товар добавлен в корзину', `${productName} успешно добавлен.`, 'success');
            const cartControls = button.closest('.cart-controls');
            cartControls.html(`
                 <div class="quantity-controls d-flex align-items-center" data-cart-id="${productId}">
                     <button class="btn btn-outline-light quantity-decrease" data-cart-id="${productId}">-</button>
                     <span class="quantity-value mx-2" data-cart-id="${productId}">1</span>
                     <button class="btn btn-outline-light quantity-increase" data-cart-id="${productId}">+</button>
                 </div>`);
        });

         productsContainer.on('click', '.quantity-increase', function () {
             const cartItemId = $(this).data('cart-id');
             const quantityValueElement = $(this).siblings('.quantity-value');
             let quantity = parseInt(quantityValueElement.text(), 10);
             quantity++;
             quantityValueElement.text(quantity);
             updateCartQuantity(cartItemId, quantity);
         });

         productsContainer.on('click', '.quantity-decrease', function () {
             const cartItemId = $(this).data('cart-id');
             const quantityValueElement = $(this).siblings('.quantity-value');
             let quantity = parseInt(quantityValueElement.text(), 10);
             if (quantity > 1) {
                     quantity--;
                     quantityValueElement.text(quantity);
                     updateCartQuantity(cartItemId, quantity);
                 } else {
                     const cartControls = $(this).closest('.cart-controls');
                     const productId = cartControls.data('product-id');
                     cartControls.html(`
             <button class="btn btn-outline-light add-to-cart-btn w-100" data-product-id="${productId}">
                 <i class="fas fa-shopping-bag"></i> В корзину
             </button>`);
                     updateCartQuantity(cartItemId, 0);
                 }
         });

        loadProducts();
    });
</script>

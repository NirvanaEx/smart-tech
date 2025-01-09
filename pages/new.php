<style>
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
</style>
<!-- Модальное окно -->
<div class="swiper">
    <div class="swiper-wrapper">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <div class="swiper-slide">
                <div class="card bg-secondary text-light">
                    <img src="upload/no_image_placeholder.jpg" class="card-img-top" alt="Продукт <?= $i ?>">
                    <div class="card-body">
                        <h5 class="card-title">Продукт <?= $i ?></h5>
                        <p class="card-text">Описание новинки</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>1 000 000 сум</span>
                            <div class="cart-controls" data-product-id="<?= $i ?>">
                                <button class="btn btn-dark add-to-cart-btn" data-product-id="<?= $i ?>">
                                    <i class="fas fa-shopping-bag"></i> В корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <!-- Навигация -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <!-- Пагинация -->
    <div class="swiper-pagination"></div>
</div>

<script>
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const productId = e.currentTarget.dataset.productId;
            const cartControls = document.querySelector(`.cart-controls[data-product-id="${productId}"]`);

            // Заменяем кнопку на элементы управления количеством
            cartControls.innerHTML = `
                <div class="quantity-controls d-flex align-items-center">
                    <button class="btn btn-outline-light quantity-decrease" data-product-id="${productId}">-</button>
                    <span class="quantity-value mx-2" data-product-id="${productId}">1</span>
                    <button class="btn btn-outline-light quantity-increase" data-product-id="${productId}">+</button>
                </div>
            `;

            // Обновляем логику для кнопок + и -
            updateQuantityControls(productId);
        });
    });

    function updateQuantityControls(productId) {
        const quantityValueElement = document.querySelector(`.quantity-value[data-product-id="${productId}"]`);
        const decreaseButton = document.querySelector(`.quantity-decrease[data-product-id="${productId}"]`);
        const increaseButton = document.querySelector(`.quantity-increase[data-product-id="${productId}"]`);
        const cartControls = document.querySelector(`.cart-controls[data-product-id="${productId}"]`);

        let quantity = parseInt(quantityValueElement.textContent, 10);

        decreaseButton.addEventListener('click', () => {
            if (quantity > 0) {
                quantity--;
                quantityValueElement.textContent = quantity;
            }
            if (quantity === 0) {
                // Если количество равно 0, заменяем элементы управления обратно на кнопку
                cartControls.innerHTML = `
                    <button class="btn btn-dark add-to-cart-btn" data-product-id="${productId}">
                        <i class="fas fa-shopping-bag"></i> В корзину
                    </button>
                `;
                // Повторно инициализируем кнопку "В корзину"
                initAddToCartButton(cartControls);
            }
        });

        increaseButton.addEventListener('click', () => {
            quantity++;
            quantityValueElement.textContent = quantity;
        });
    }

    function initAddToCartButton(cartControls) {
        const addToCartButton = cartControls.querySelector('.add-to-cart-btn');
        addToCartButton.addEventListener('click', (e) => {
            const productId = e.currentTarget.dataset.productId;
            const cartControls = document.querySelector(`.cart-controls[data-product-id="${productId}"]`);

            // Заменяем кнопку на элементы управления количеством
            cartControls.innerHTML = `
                <div class="quantity-controls d-flex align-items-center">
                    <button class="btn btn-outline-light quantity-decrease" data-product-id="${productId}">-</button>
                    <span class="quantity-value mx-2" data-product-id="${productId}">1</span>
                    <button class="btn btn-outline-light quantity-increase" data-product-id="${productId}">+</button>
                </div>
            `;

            // Обновляем логику для кнопок + и -
            updateQuantityControls(productId);
        });
    }

    console.log('Initializing Swiper');
    if (typeof Swiper !== 'undefined') {
        new Swiper('.swiper', {
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
            },
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 10 },
                768: { slidesPerView: 3, spaceBetween: 10 },
                1024: { slidesPerView: 4, spaceBetween: 10 },
            },
        });
    } else {
        console.error('Swiper is not loaded');
    }
</script>

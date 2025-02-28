(function(){
    // Добавляем стили для карусели, карточек и кнопок
    const style = document.createElement('style');
    style.innerHTML = `
        /* Контейнер карусели с отступом для пагинации */
        .swiper {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            padding-bottom: 40px; /* Отступ для пагинации */
        }
        .swiper-wrapper {
            display: flex;
        }
        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            width: auto;
        }
        .swiper-slide .card {
            max-width: 350px;
            margin: 0 auto;
        }
        .swiper-slide img {
            border-radius: 10px;
            object-fit: cover;
            width: 100%;
            max-height: 220px;
        }
        /* Сдвигаем стрелки, чтобы они не перекрывали карточки */
        .swiper-button-prev, .swiper-button-next {
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 30px;
            height: 30px;
        }
        .swiper-button-prev {
            left: 5px;
        }
        .swiper-button-next {
            right: 5px;
        }
        /* Пагинация располагается внизу */
        .swiper-pagination {
            bottom: 0 !important;
        }
        /* Контейнер для цены и кнопки "В корзину" */
        .price-container {
            text-align: right;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .cart-button-container {
            text-align: right;
        }
    `;
    document.head.appendChild(style);

    // Загружает последние 5 товаров (новинки)
    function loadNewProducts() {
        fetch(`${BASE_URL}products?new=1&limit=5`)
            .then(response => {
                if (!response.ok) throw new Error("Ошибка загрузки товаров");
                return response.json();
            })
            .then(data => {
                if (data.status !== 200) throw new Error(data.message);
                const products = data.data;
                const wrapper = document.getElementById('newProductsWrapper');
                if (!wrapper) {
                    console.error("Элемент с id 'newProductsWrapper' не найден.");
                    return;
                }
                wrapper.innerHTML = "";
                products.forEach(product => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.innerHTML = `
                        <div class="card bg-secondary text-light">
                            <img src="${product.image_url}" class="card-img-top" alt="${product.product_name}">
                            <div class="card-body">
                                <h5 class="card-title">${product.product_name}</h5>
                                <p class="card-text">${product.description}</p>
                                <div class="price-container">
                                    ${product.price} сум
                                </div>
                                <div class="cart-button-container cart-controls" data-product-id="${product.id}">
                                    <button class="btn btn-dark add-to-cart-btn" data-product-id="${product.id}">
                                        <i class="fas fa-shopping-bag"></i> В корзину
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    wrapper.appendChild(slide);
                });
                initNewProductsCart();
                if(window.newSwiper) {
                    window.newSwiper.destroy(true, true);
                }
                window.newSwiper = new Swiper('.swiper', {
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
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    breakpoints: {
                        640: { slidesPerView: 2, spaceBetween: 10 },
                        768: { slidesPerView: 3, spaceBetween: 10 },
                        1024: { slidesPerView: 4, spaceBetween: 10 },
                    },
                });
                // После загрузки карточек синхронизируем view с текущей корзиной
                syncNewProductsCartView();
            })
            .catch(error => {
                console.error("Ошибка: ", error);
            });
    }

    // Инициализация обработчиков для кнопок "В корзину" в новинках
    function initNewProductsCart() {
        document.querySelectorAll('#new-products-area .add-to-cart-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const productId = e.currentTarget.dataset.productId;
                const productName = e.currentTarget.closest('.card-body').querySelector('.card-title').textContent;
                // Вызываем глобальную функцию addToCart (из part-index-cart.js)
                addToCart({ id: productId, name: productName });
                // Через небольшую задержку обновляем корзину, чтобы синхронизация прошла
                setTimeout(() => {
                    const userId = getUserId();
                    if (userId) {
                        fetchCart(userId);
                        // После обновления корзины синхронизируем отображение в новинках
                        setTimeout(syncNewProductsCartView, 500);
                    }
                }, 500);
            });
        });
    }

    // Синхронизирует отображение новинок с глобальной переменной cart (которая обновляется в part-index-cart.js)
    function syncNewProductsCartView() {
        document.querySelectorAll('#new-products-area .cart-controls').forEach(control => {
            const productId = control.getAttribute('data-product-id');
            // Ищем товар в глобальной переменной cart
            const cartItem = (Array.isArray(cart) && cart.length > 0)
                ? cart.find(item => parseInt(item.product_id) === parseInt(productId))
                : null;
            if (cartItem) {
                control.innerHTML = `
                <div class="quantity-controls d-flex align-items-center">
                    <button class="btn btn-outline-light quantity-decrease" data-cart-id="${cartItem.id}" data-product-id="${productId}">-</button>
                    <span class="quantity-value mx-2" data-cart-id="${cartItem.id}" data-product-id="${productId}">${cartItem.quantity}</span>
                    <button class="btn btn-outline-light quantity-increase" data-cart-id="${cartItem.id}" data-product-id="${productId}">+</button>
                </div>
            `;
            } else {
                control.innerHTML = `
                <button class="btn btn-dark add-to-cart-btn" data-product-id="${productId}">
                    <i class="fas fa-shopping-bag"></i> В корзину
                </button>
            `;
            }
        });
        // Повторно инициализируем обработчики для кнопок "В корзину"
        initNewProductsCart();
    }

    // Делегирование событий для кнопок увеличения/уменьшения количества в новинках
    document.getElementById('new-products-area').addEventListener('click', function(e) {
        let incBtn = e.target.closest('.quantity-increase');
        let decBtn = e.target.closest('.quantity-decrease');
        if (incBtn) {
            const cartId = incBtn.dataset.cartId;
            const quantitySpan = document.querySelector(`.quantity-value[data-cart-id="${cartId}"]`);
            let quantity = parseInt(quantitySpan.textContent, 10);
            quantity++;
            quantitySpan.textContent = quantity;
            updateCartQuantity(cartId, quantity); // Функция из part-index-cart.js
        }
        if (decBtn) {
            const cartId = decBtn.dataset.cartId;
            const quantitySpan = document.querySelector(`.quantity-value[data-cart-id="${cartId}"]`);
            let quantity = parseInt(quantitySpan.textContent, 10);
            if (quantity > 1) {
                quantity--;
                quantitySpan.textContent = quantity;
                updateCartQuantity(cartId, quantity);
            } else {
                const controls = decBtn.closest('.cart-controls');
                const productId = controls.dataset.productId;
                controls.innerHTML = `<button class="btn btn-dark add-to-cart-btn" data-product-id="${productId}">
                                            <i class="fas fa-shopping-bag"></i> В корзину
                                      </button>`;
                initNewProductsCart();
                updateCartQuantity(cartId, 0);
            }
        }
    });

    // При первичной загрузке синхронизируем корзину и отображение новинок
    document.addEventListener('DOMContentLoaded', () => {
        loadNewProducts();
        const userId = getUserId();
        if (userId) {
            fetchCart(userId);
            // Через небольшую задержку обновляем отображение новинок
            setTimeout(syncNewProductsCartView, 500);
        }
    });

    window.loadNewProducts = loadNewProducts;
})();

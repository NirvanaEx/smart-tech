// Инициализация кнопок "Избранное"
function initializeFavoriteButtons() {
    console.log('Инициализация кнопок избранного');

    document.querySelectorAll('.btn-favorite').forEach(button => {
        button.addEventListener('click', function () {
            console.log('Кнопка избранного нажата:', this); // Логируем нажатие
            this.classList.toggle('active'); // Переключаем класс active
        });
    });
}

// Инициализация кнопок "В корзинку"
function initializeCartButtons() {
    console.log('Инициализация кнопок корзинки');

    document.querySelectorAll('.btn-cart').forEach(button => {
        button.addEventListener('click', function () {
            if (this.classList.contains('active')) return;

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

            // Увеличение количества
            increaseButton.addEventListener('click', function (event) {
                event.stopPropagation();
                let quantity = parseInt(quantityElement.textContent, 10);
                quantityElement.textContent = ++quantity;
            });

            // Уменьшение количества
            decreaseButton.addEventListener('click', function (event) {
                event.stopPropagation();
                let quantity = parseInt(quantityElement.textContent, 10);
                if (quantity > 1) {
                    quantityElement.textContent = --quantity;
                } else {
                    button.classList.remove('active');
                    button.innerHTML = `<i class="fas fa-cart-plus"></i> В корзинку`;
                }
            });

            // Увеличение общего количества в корзине
            updateCartCount(1);
        });
    });
}

// Обновление количества товаров в корзине
function updateCartCount(change) {
    const cartCountElement = document.getElementById('cart-count');
    let currentCount = parseInt(cartCountElement.textContent, 10) || 0;
    currentCount += change;
    cartCountElement.textContent = currentCount;

    if (currentCount > 0) {
        cartCountElement.style.display = 'inline-block';
    } else {
        cartCountElement.style.display = 'none';
    }
}

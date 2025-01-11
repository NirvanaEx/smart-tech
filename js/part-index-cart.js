import { BASE_URL } from './config/config.js';

// Глобальная переменная для хранения корзины
let cart = [];

// Инициализация обработчиков событий
document.addEventListener('DOMContentLoaded', () => {
    const cartButton = document.getElementById('cartButton');
    if (cartButton) {
        cartButton.addEventListener('click', showCart);
    } else {
        console.error("Кнопка 'Корзина' не найдена в DOM.");
    }
    // Загружаем корзину при загрузке страницы
    const userId = getUserId();
    if (userId) fetchCart(userId);
});

// Функция получения корзины с сервера
function fetchCart(userId) {
    fetch(`${BASE_URL}cart/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                cart = data.data;
                updateCartCount();
            } else {
                console.error('Ошибка загрузки корзины:', data.message);
                cart = [];
                updateCartCount();
            }
        })
        .catch(error => {
            console.error('Ошибка подключения к серверу:', error);
            cart = [];
            updateCartCount();
        });
}

// Функция отображения корзины
function showCart() {
    const userId = getUserId();
    if (!userId) {
        Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
        return;
    }

    Swal.fire({
        title: 'Загрузка...',
        html: '<div class="spinner-border text-light" role="status"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    // Загружаем данные корзины перед отображением
    fetch(`${BASE_URL}cart/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                cart = data.data;

                let cartHTML = '<table class="table table-dark table-striped">';
                cartHTML += '<thead><tr><th>Изображение</th><th>Название</th><th>Цена</th><th>Количество</th><th>Действия</th></tr></thead>';
                cartHTML += '<tbody>';

                let total = 0;

                if (cart.length > 0) {
                    cart.forEach(product => {
                        cartHTML += `
                            <tr>
                                <td><img src="${product.image_path}" alt="${product.product_name}" style="height: 50px;"></td>
                                <td>${product.product_name}</td>
                                <td>${product.price} ₽</td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm quantity-input" 
                                           data-cart-id="${product.id}" 
                                           value="${product.quantity}" 
                                           min="1" 
                                           style="width: 60px;">
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" data-cart-id="${product.id}">Удалить</button>
                                </td>
                            </tr>`;
                        total += product.price * product.quantity;
                    });
                } else {
                    cartHTML += '<tr><td colspan="5" class="text-center">Корзина пуста</td></tr>';
                }

                cartHTML += '</tbody></table>';
                cartHTML += `<div class="text-end fw-bold">Итого: ${total} ₽</div>`;

                // Отображаем SweetAlert с корзиной
                Swal.fire({
                    title: 'Корзина',
                    html: cartHTML,
                    showCancelButton: true,
                    confirmButtonText: 'Оформить заказ',
                    cancelButtonText: 'Закрыть',
                    showDenyButton: true,
                    denyButtonText: 'Очистить корзину',
                    customClass: {
                        popup: 'bg-dark text-light',
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        processOrder();
                    } else if (result.isDenied) {
                        clearCart(userId);
                    }
                });

                // Устанавливаем обработчики на input для изменения количества
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('change', (event) => {
                        const cartItemId = event.target.dataset.cartId;
                        const quantity = parseInt(event.target.value);
                        if (quantity > 0) {
                            updateCartQuantity(cartItemId, quantity);
                        } else {
                            Swal.fire('Ошибка', 'Количество должно быть больше 0', 'error');
                            event.target.value = 1; // Сбрасываем значение
                        }
                    });
                });

                // Устанавливаем обработчики на кнопки удаления
                document.querySelectorAll('.btn-danger').forEach(button => {
                    button.addEventListener('click', (event) => {
                        const cartItemId = event.target.dataset.cartId;
                        updateCartQuantity(cartItemId, 0); // Устанавливаем количество в 0 для удаления
                    });
                });
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось загрузить данные корзины', 'error');
        });
}


// Функция добавления продукта в корзину
function addToCart(productId, quantity = 1) {
    const userId = getUserId();
    if (!userId) {
        Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
        return;
    }

    fetch(`${BASE_URL}cart`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, product_id: productId, quantity })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 201 || data.status === 200) {
                fetchCart(userId);
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось добавить продукт в корзину', 'error');
        });
}

// Функция обновления количества продукта в корзине
function updateCartQuantity(cartItemId, quantity) {
    const userId = getUserId();

    fetch(`${BASE_URL}cart/${cartItemId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ quantity })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                fetchCart(userId);
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось обновить количество продукта', 'error');
        });
}

// Функция очистки корзины
function clearCart(userId) {
    fetch(`${BASE_URL}cart/${userId}`, {
        method: 'DELETE'
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                fetchCart(userId);
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось очистить корзину', 'error');
        });
}

// Функция обработки заказа
function processOrder() {
    Swal.fire('Успешно!', 'Заказ оформлен!', 'success');
    clearCart(getUserId());
}

// Обновление количества товаров в корзине
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCountElement.textContent = totalItems;
}

// Получение ID текущего пользователя
function getUserId() {
    const user = JSON.parse(localStorage.getItem('user'));
    return user ? user.user_id : null;
}

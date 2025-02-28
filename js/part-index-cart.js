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
                syncCartWithUI();
                updateCartCount();
                if (typeof window.syncNewProductsCartView === 'function') {
                    window.syncNewProductsCartView();
                }
            } else {
                console.error('Ошибка загрузки корзины:', data.message);
                cart = [];
                updateCartCount();
                if (typeof window.syncNewProductsCartView === 'function') {
                    window.syncNewProductsCartView();
                }
            }
        })
        .catch(error => {
            console.error('Ошибка подключения к серверу:', error);
            cart = [];
            updateCartCount();
            if (typeof window.syncNewProductsCartView === 'function') {
                window.syncNewProductsCartView();
            }
        });
}

function addToCart(product) {
    const userId = getUserId();
    if (!userId) {
        Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
        return;
    }
    fetch(`${BASE_URL}cart`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: userId,
            product_id: product.id,
            quantity: 1
        })
    })
        .then(response => {
           return response.json().then(data => {
                   // Если сервер вернул не успешный статус, выбрасываем ошибку с подробностями
                       if (!(data.status === 200 || data.status === 201)) {
                           throw new Error(data.message || "Ошибка при добавлении товара");
                       }
                   return data;
           });
        })
        .then(data => {
            if (data.status === 200 || data.status === 201) {
                Swal.fire('Товар добавлен в корзину', `${product.name} успешно добавлен.`, 'success');
                fetchCart(userId);
                if (typeof fetchFavorites === 'function') {
                    fetchFavorites();
                }
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка добавления товара в корзину:', error);
            // Если товар уже добавился (проверьте логи сервера), можно не показывать ошибку или показать предупреждение
            Swal.fire('Предупреждение', error.message, 'warning');
        });
}

// Синхронизация корзины с пользовательским интерфейсом
function syncCartWithUI() {
    cart.forEach(cartItem => {
        const cartControls = $(`.cart-controls[data-product-id="${cartItem.product_id}"]`);
        if (cartControls.length) {
             cartControls.html(`
                 <div class="quantity-controls d-flex align-items-center">
                     <button class="btn btn-outline-light quantity-decrease" data-cart-id="${cartItem.id}">-</button>
                     <span class="quantity-value mx-2" data-cart-id="${cartItem.id}">${cartItem.quantity}</span>
                     <button class="btn btn-outline-light quantity-increase" data-cart-id="${cartItem.id}">+</button>
                 </div>
             `);
        }
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
                                <td>${product.price} сум</td>
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
                cartHTML += `<div class="text-end fw-bold">Итого: ${total} сум</div>`;

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

                // Обработчики изменения количества в корзине
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('change', (event) => {
                        const cartItemId = event.target.dataset.cartId;
                        const quantity = parseInt(event.target.value);
                        if (quantity > 0) {
                            updateCartQuantity(cartItemId, quantity);
                        } else {
                            Swal.fire('Ошибка', 'Количество должно быть больше 0', 'error');
                            event.target.value = 1;
                        }
                    });
                });

                // Обработчики кнопок удаления в корзине
                document.querySelectorAll('.btn-danger').forEach(button => {
                    button.addEventListener('click', (event) => {
                        const cartItemId = event.target.dataset.cartId;
                        updateCartQuantity(cartItemId, 0);
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

// Функция обновления количества продукта
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
                fetchCart(userId); // Синхронизируем корзину
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось обновить количество продукта', 'error');
        });
}



function updateCartView() {
    const userId = getUserId();
    if (userId) {
        fetchCart(userId);  // обновляем данные корзины с сервера
    }
    loadProducts();         // перерисовываем карточки товаров
    updateCartCount();      // обновляем счетчик в шапке
}

function clearCart(userId) {
    fetch(`${BASE_URL}cart/${userId}`, {
        method: 'DELETE'
    })
        .then(response => response.json())
        .then(data => {
            if (data.status == 200) {
                Swal.fire('Корзина очищена', data.message, 'success').then(() => {
                    cart = [];
                    updateCartView();
                    // Задержка, затем обновляем view новинок
                    setTimeout(() => {
                        if (typeof window.loadNewProducts === 'function') {
                            window.loadNewProducts();
                        }
                    }, 500);
                });
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка при очистке корзины:', error);
            Swal.fire('Ошибка', 'Не удалось очистить корзину', 'error');
        });
}


function processOrder() {
    const userId = getUserId();
    if (!userId) {
        Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
        return;
    }
    if (cart.length === 0) {
        Swal.fire('Ошибка', 'Корзина пуста', 'error');
        return;
    }

    // Для каждого товара из корзины отправляем данные на сервер, где внутри addUserOrder
    // происходит: получение данных из products, создание записи в product_versions, расчёт total_price и оформление заказа.
    const orderPromises = cart.map(item => {
        const orderData = {
            product_id: item.product_id,  // идентификатор продукта
            user_id: userId,              // идентификатор пользователя
            quantity: item.quantity       // количество товара
        };

        return fetch(`${BASE_URL}user-orders`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        }).then(response => response.json());
    });

    Promise.all(orderPromises)
        .then(results => {
            // Проверяем, что все заказы создались успешно (ответы с кодом 200 или 201)
            const failedOrders = results.filter(result => !(result.status === 200 || result.status === 201));
            if (failedOrders.length === 0) {
                Swal.fire('Успех', 'Заказ оформлен успешно', 'success')
                    .then(() => {
                        clearCart(userId);
                        updateCartView();

                        // При необходимости можно перенаправить пользователя на страницу заказов:
                        // window.location.href = 'orders.html';
                    });
            } else {
                Swal.fire('Ошибка', 'Не удалось оформить заказ для некоторых товаров', 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка при оформлении заказа:', error);
            Swal.fire('Ошибка', 'Произошла ошибка при оформлении заказа', 'error');
        });
}


// Функция для отображения количества товаров в корзине
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    const totalItems = cart.reduce((sum, item) => sum + Number(item.quantity), 0);
    cartCountElement.textContent = totalItems;
}


// Функция для получения ID пользователя
function getUserId() {
    const user = JSON.parse(localStorage.getItem('user'));
    return user ? user.user_id : null;
}

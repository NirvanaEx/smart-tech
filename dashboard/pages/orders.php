<style>
    .table-container {
        margin-top: 20px;
    }

    /* Custom table style */
    table {
        border-collapse: collapse; /* Убираем белые разделяющие линии */
        width: 100%;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        overflow: hidden;
        background-color: #ffffff;
    }

    thead {
        background-color: #343a40; /* Темный фон */
        color: #ffffff; /* Белый текст */
    }

    th, td {
        padding: 8px;
        text-align: center;
    }

    tbody tr:hover {
        background-color: #f1f3f5;
    }

    tbody td {
        vertical-align: middle;
        text-align: center;
    }

    .pagination-container {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    .pagination-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid #ced4da;
        border-radius: 50%;
        background-color: #ffffff;
        cursor: pointer;
        font-size: 14px;
        color: #495057;
    }

    .pagination-btn.active {
        background-color: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
    }

    .pagination-btn.disabled {
        pointer-events: none;
        opacity: 0.5;
    }

    .search-container {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-end;
    }

    .search-container input {
        width: 300px;
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        table {
            font-size: 14px;
        }

        .search-container input {
            width: 100%;
        }
    }

     .order-details-container {
         display: flex;
         flex-direction: column;
         align-items: flex-start;
         gap: 10px;
     }
    .order-detail {
        display: flex;
        justify-content: space-between;
        width: 100%;
    }
    .order-label {
        font-weight: bold;
        flex: 0 0 40%;
    }
    .order-value {
        flex: 1;
        text-align: right;
    }
    select {
        width: 100%;
        padding: 5px;
        font-size: 14px;
    }

</style>

<div class="container">
    <h2 class="my-4">Orders</h2>
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search orders..." oninput="fetchOrders()">
    </div>
    <div class="table-container">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>User</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody id="orders-table-body">
            <!-- Dynamic content will be loaded here -->
            </tbody>
        </table>
    </div>
    <div class="pagination-container" id="pagination">
        <!-- Pagination buttons will be loaded here -->
    </div>
</div>

<script>
    // Fetch and display orders with pagination and search
    function fetchOrders() {
        const itemsPerPage = 30;
        const searchQuery = document.getElementById('searchInput').value.trim().toLowerCase();
        const activePageButton = document.querySelector('.pagination-btn.active');
        const currentPage = activePageButton ? parseInt(activePageButton.textContent, 10) : 1;

        fetch('http://smart-tech/API/orders')
            .then(response => response.json())
            .then(data => {
                if (data.status === 200) {
                    const tbody = document.getElementById('orders-table-body');
                    const pagination = document.getElementById('pagination');

                    const filteredData = data.data.filter(order =>
                        order.product_name.toLowerCase().includes(searchQuery) ||
                        order.user_name.toLowerCase().includes(searchQuery) ||
                        order.status.toLowerCase().includes(searchQuery) ||
                        order.payment_status.toLowerCase().includes(searchQuery)
                    );

                    const totalItems = filteredData.length;
                    const totalPages = Math.ceil(totalItems / itemsPerPage);
                    const startIndex = (currentPage - 1) * itemsPerPage;
                    const endIndex = startIndex + itemsPerPage;
                    const paginatedData = filteredData.slice(startIndex, endIndex);

                    tbody.innerHTML = '';

                    if (paginatedData.length > 0) {
                        paginatedData.forEach(order => {
                            const row = `
                            <tr id="order-${order.id}" onclick="showOrderDetails(${order.id})">
                                <td>${order.id}</td>
                                <td>${order.product_name} (${order.product_version})</td>
                                <td>${order.user_name}</td>
                                <td>${order.quantity}</td>
                                <td>${order.total_price}</td>
                                <td>${order.status}</td>
                                <td>${order.payment_status}</td>
                                <td>${order.date_creation}</td>
                            </tr>
                        `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No orders available.</td></tr>';
                    }

                    pagination.innerHTML = `
                    <div class="pagination-btn ${currentPage === 1 ? 'disabled' : ''}" onclick="changePage(${currentPage - 1})">
                        <i class="fas fa-chevron-left"></i>
                    </div>`;
                    for (let i = 1; i <= totalPages; i++) {
                        pagination.innerHTML += `
                        <div class="pagination-btn ${currentPage === i ? 'active' : ''}" onclick="changePage(${i})">${i}</div>`;
                    }
                    pagination.innerHTML += `
                    <div class="pagination-btn ${currentPage === totalPages ? 'disabled' : ''}" onclick="changePage(${currentPage + 1})">
                        <i class="fas fa-chevron-right"></i>
                    </div>`;
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to fetch orders', 'error');
            });
    }

    function showOrderDetails(orderId) {
        fetch(`http://smart-tech/API/orders/${orderId}`)
            .then(response => response.json())
            .then(order => {
                if (order.status === 200) {
                    const orderData = order.data;

                    // Новые значения для status и payment_status
                    const validStatuses = ['pending', 'shipped', 'delivered', 'cancelled', 'in_progress'];
                    const validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

                    Swal.fire({
                        title: `Order #${orderData.id}`,
                        html: `

                    <div class="order-details-container">
                        <div class="order-detail">
                            <span class="order-label">Product:</span>
                            <span class="order-value">${orderData.product_name} (${orderData.product_version})</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-label">User:</span>
                            <span class="order-value">${orderData.user_name || 'N/A'}</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-label">Quantity:</span>
                            <span class="order-value">${orderData.quantity}</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-label">Total Price:</span>
                            <span class="order-value">${orderData.total_price}</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-label">Status:</span>
                            <span class="order-value">
                                <select id="order-status">
                                    ${validStatuses
                            .map(
                                status => `
                                            <option value="${status}" ${orderData.status === status ? 'selected' : ''}>
                                                ${status.charAt(0).toUpperCase() + status.slice(1)}
                                            </option>`
                            )
                            .join('')}
                                </select>
                            </span>
                        </div>
                        <div class="order-detail">
                            <span class="order-label">Payment Status:</span>
                            <span class="order-value">
                                <select id="payment-status">
                                    ${validPaymentStatuses
                            .map(
                                status => `
                                            <option value="${status}" ${orderData.payment_status === status ? 'selected' : ''}>
                                                ${status.charAt(0).toUpperCase() + status.slice(1)}
                                            </option>`
                            )
                            .join('')}
                                </select>
                            </span>
                        </div>
                        <div class="order-detail">
                            <span class="order-label">Date:</span>
                            <span class="order-value">${orderData.date_creation}</span>
                        </div>
                    </div>
                `,
                        showCancelButton: true,
                        confirmButtonText: 'Save',
                        preConfirm: () => {
                            const updatedStatus = document.getElementById('order-status').value;
                            const updatedPaymentStatus = document.getElementById('payment-status').value;

                            return fetch(`http://smart-tech/API/orders/${orderId}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    status: updatedStatus,
                                    payment_status: updatedPaymentStatus,
                                }),
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Failed to update order');
                                    }
                                    return response.json();
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Request failed: ${error}`);
                                });
                        },
                    }).then(result => {
                        if (result.isConfirmed) {
                            Swal.fire('Success', 'Order updated successfully', 'success');
                            fetchOrders(); // Refresh orders
                        }
                    });
                } else {
                    Swal.fire('Error', order.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to fetch order details', 'error');
            });
    }



    function changePage(newPage) {
        const activePageButton = document.querySelector('.pagination-btn.active');
        const totalPages = document.querySelectorAll('.pagination-btn').length - 2; // Учитываем кнопки "Назад" и "Вперед"

        // Проверяем, чтобы новая страница была в допустимых пределах
        if (newPage >= 1 && newPage <= totalPages) {
            if (activePageButton) {
                activePageButton.classList.remove('active'); // Убираем активный класс с текущей страницы
            }

            // Устанавливаем новый активный класс для выбранной страницы
            const newPageButton = [...document.querySelectorAll('.pagination-btn')]
                .find(button => parseInt(button.textContent, 10) === newPage);
            if (newPageButton) {
                newPageButton.classList.add('active');
            }

            fetchOrders(); // Обновляем данные
        }
    }

    // Загружаем заказы при загрузке страницы
    fetchOrders();
</script>

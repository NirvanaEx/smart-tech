<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
        }
        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .sidebar a.active, .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            padding: 20px;
        }
        .table {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4>Admin Panel</h4>
            <a href="#" class="menu-item active" data-page="pages/users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="#" class="menu-item" data-page="pages/products.php"><i class="fas fa-boxes"></i> Manage Products</a>
            <a href="#" class="menu-item" data-page="pages/orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a>
        </div>
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 content">
            <div id="content">
                <h2>Welcome to the Admin Panel</h2>
                <p>Select an option from the menu to get started.</p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JS -->
<script>
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all menu items
            document.querySelectorAll('.menu-item').forEach(link => link.classList.remove('active'));
            this.classList.add('active');

            // Load the selected page
            const page = this.getAttribute('data-page');
            fetch(page)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('content').innerHTML = html;

                    // Если загружается users.php, вызовите соответствующий скрипт
                    if (page.includes('users.php')) {
                        setupUsersPage();
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to load the page', 'error');
                });
        });
    });

    // Функция для работы с users.php
    function setupUsersPage() {
        // Fetch and display users
        function fetchUsers() {
            fetch('http://smart-tech/API/users')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        const tbody = document.getElementById('usersTableBody');
                        tbody.innerHTML = ''; // Clear existing rows
                        data.data.forEach(user => {
                            const row = `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.login}</td>
                                    <td>${user.role}</td>
                                    <td>${user.date_creation}</td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to fetch users', 'error');
                });
        }

        // Add new user
        document.getElementById('addUserBtn').addEventListener('click', () => {
            Swal.fire({
                title: 'Add New User',
                html: `
                    <input type="text" id="login" class="swal2-input" placeholder="Login">
                    <input type="password" id="password" class="swal2-input" placeholder="Password">
                    <select id="role" class="swal2-input">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                `,
                confirmButtonText: 'Add',
                preConfirm: () => {
                    const login = document.getElementById('login').value;
                    const password = document.getElementById('password').value;
                    const role = document.getElementById('role').value;

                    if (!login || !password || !role) {
                        Swal.showValidationMessage('All fields are required');
                        return false;
                    }

                    return { login, password, role };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    const user = result.value;

                    // Send data to API
                    fetch('http://smart-tech/API/users', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(user)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 201) {
                                Swal.fire('Success', 'User added successfully', 'success');
                                fetchUsers(); // Refresh users list
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Failed to add user', 'error');
                        });
                }
            });
        });

        // Load users on page load
        fetchUsers();
    }
</script>
</body>
</html>

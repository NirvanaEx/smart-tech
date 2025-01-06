<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* General styles */
        body {
            background-color: #f8f9fa;
            overflow: hidden; /* Убираем глобальную прокрутку страницы */
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
            position: fixed; /* Фиксируем боковое меню */
            top: 0;
            left: 0;
            width: 16.6667%; /* Ширина 2 колонки для Bootstrap */
        }

        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 16.6667%; /* Учитываем ширину бокового меню */
            height: 100vh;
            overflow-y: auto; /* Добавляем вертикальную прокрутку */
            padding: 20px;
        }

        .table {
            background-color: #ffffff;
        }

        /* Category-specific styles */
        #categoriesContent h1 {
            color: #007bff;
        }

        /* Subcategory-specific styles */
        #subcategoriesContent h1 {
            color: #28a745;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4>Dashboard</h4>
            <a href="#" class="menu-item active" data-page="category"><i class="fas fa-folder"></i> Categories</a>
            <a href="#" class="menu-item" data-page="subcategory"><i class="fas fa-folder-open"></i> Subcategories</a>
            <a href="#" class="menu-item" data-page="products"><i class="fas fa-box"></i> Products</a>
        </div>
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 content">
            <div id="content">
                <h2>Welcome to the Dashboard</h2>
                <p>Select an option from the menu to get started.</p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // General JS
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            // Toggle active class in sidebar
            document.querySelectorAll('.menu-item').forEach(link => link.classList.remove('active'));
            this.classList.add('active');

            // Load the selected page dynamically
            const page = this.getAttribute('data-page');
            loadPage(page);
        });
    });

    // Function to load pages dynamically
    function loadPage(page) {
        fetch(`pages/${page}.php`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load page');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('content').innerHTML = html;

                // Initialize scripts for specific pages
                if (page === 'category') {
                    initializeCategory();
                } else if (page === 'subcategory') {
                    initializeSubcategory();
                } else if (page === 'products') {
                    initializeProducts();
                }
            })
            .catch(error => {
                console.error('Error loading page:', error);
                Swal.fire('Error', 'Failed to load the page', 'error');
            });
    }

    // Category-specific JS
    function initializeCategory() {
        // Fetch and display categories
        function fetchCategories() {
            fetch('http://smart-tech/API/categories')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        const tbody = document.getElementById('categoriesTableBody');
                        tbody.innerHTML = '';
                        data.data.forEach(category => {
                            const row = `
                                <tr id="category-${category.id}">
                                    <td>${category.id}</td>
                                    <td>${category.name}</td>
                                    <td>${category.date_creation}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="deleteCategory(${category.id})">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to fetch categories', 'error');
                });
        }

        // Delete category
        function deleteCategory(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`http://smart-tech/API/categories/${id}`, {
                        method: 'DELETE',
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 200) {
                                Swal.fire('Deleted!', 'Category has been deleted.', 'success');
                                document.getElementById(`category-${id}`).remove();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Failed to delete category', 'error');
                        });
                }
            });
        }

        // Add event listener for adding categories
        document.getElementById('addCategoryBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Add New Category',
                html: `<input type="text" id="categoryName" class="swal2-input" placeholder="Category Name">`,
                confirmButtonText: 'Add',
                preConfirm: () => {
                    const name = document.getElementById('categoryName').value;
                    if (!name) {
                        Swal.showValidationMessage('Category Name is required');
                        return false;
                    }
                    return { name };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('http://smart-tech/API/categories', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(result.value)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 201) {
                                Swal.fire('Success', 'Category added successfully', 'success');
                                fetchCategories();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Failed to add category', 'error');
                        });
                }
            });
        });

        // Fetch categories on page load
        fetchCategories();
    }

    // Subcategory-specific JS
    function initializeSubcategory() {
        // Fetch and display subcategories
        function fetchSubcategories() {
            fetch('http://smart-tech/API/subcategories')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        const tbody = document.getElementById('subcategoriesTableBody');
                        tbody.innerHTML = '';
                        data.data.forEach(subcategory => {
                            const row = `
                                <tr id="subcategory-${subcategory.id}">
                                    <td>${subcategory.id}</td>
                                    <td>${subcategory.category_name}</td>
                                    <td>${subcategory.subcategory_name}</td>
                                    <td>${subcategory.date_creation}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="deleteSubcategory(${subcategory.id})">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to fetch subcategories', 'error');
                });
        }

        // Add new subcategory
        function addSubcategory() {
            fetch('http://smart-tech/API/categories')
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 200) {
                        Swal.fire('Error', 'Failed to load categories for subcategory creation', 'error');
                        return;
                    }

                    const categoryOptions = data.data.map(
                        category => `<option value="${category.id}">${category.name}</option>`
                    ).join('');

                    Swal.fire({
                        title: 'Add New Subcategory',
                        html: `
                    <select id="categoryId" class="swal2-input">
                        <option value="" disabled selected>Select Category</option>
                        ${categoryOptions}
                    </select>
                    <input type="text" id="subcategoryName" class="swal2-input" placeholder="Subcategory Name">
                `,
                        confirmButtonText: 'Add',
                        preConfirm: () => {
                            const categoryId = document.getElementById('categoryId').value;
                            const name = document.getElementById('subcategoryName').value;

                            if (!categoryId || !name) {
                                Swal.showValidationMessage('All fields are required');
                                return false;
                            }

                            return { category_id: categoryId, name };
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch('http://smart-tech/API/subcategories', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(result.value)
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 201) {
                                        Swal.fire('Success', 'Subcategory added successfully', 'success');
                                        fetchSubcategories(); // Refresh subcategories list
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Failed to add subcategory', 'error');
                                });
                        }
                    });
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to fetch categories', 'error');
                });
        }

        // Delete subcategory
        function deleteSubcategory(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`http://smart-tech/API/subcategories/${id}`, {
                        method: 'DELETE',
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 200) {
                                Swal.fire('Deleted!', 'Subcategory has been deleted.', 'success');
                                document.getElementById(`subcategory-${id}`).remove();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Failed to delete subcategory', 'error');
                        });
                }
            });
        }

        // Event listener for adding subcategory
        document.getElementById('addSubcategoryBtn').addEventListener('click', addSubcategory);

        // Fetch subcategories on page load
        fetchSubcategories();
    }


    // Fetch products
    function fetchProducts() {
        fetch('http://smart-tech/API/products')
            .then(response => response.json())
            .then(data => {
                if (data.status === 200) {
                    const tbody = document.getElementById('productsTableBody');
                    tbody.innerHTML = ''; // Clear table before adding new data
                    data.data.forEach(product => {
                        const row = `
                            <tr id="product-${product.id}">
                                <td>${product.id}</td>
                                <td>${product.product_name}</td>
                                <td>${product.description}</td>
                                <td>${product.quantity}</td>
                                <td>${product.data_status}</td>
                                <td>${product.category_name}</td>
                                <td>${product.subcategory_name}</td>
                                <td>${product.price}</td>
                                <td>
                                    <img src="${product.image_url}" alt="${product.product_name}" style="width: 50px; height: 50px;">
                                </td>
                                 <td>
                                    <button class="btn btn-primary btn-sm" onclick="editProduct(${product.id})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteProduct(${product.id})">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to fetch products', 'error');
            });
    }

    // Product-specific JS
    function initializeProducts() {
        console.log('Initializing Products Page');


        // Add product
        function addProduct() {
            fetch('http://smart-tech/API/subcategories')
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 200) {
                        Swal.fire('Error', 'Failed to load subcategories for product creation', 'error');
                        return;
                    }

                    const subcategoryOptions = data.data.map(
                        subcategory => `<option value="${subcategory.id}">${subcategory.subcategory_name}</option>`
                    ).join('');

                    Swal.fire({
                        title: 'Add New Product',
                        html: `
                            <form id="addProductForm" style="text-align: left;">
                                <label for="subcategoryId" style="display: block; margin-bottom: 5px;">Subcategory</label>
                                <select id="subcategoryId" class="form-control" style="width: 100%; margin-bottom: 10px;">
                                    <option value="" disabled selected>Select Subcategory</option>
                                    ${subcategoryOptions}
                                </select>
                                <label for="productName" style="display: block; margin-bottom: 5px;">Product Name</label>
                                <input type="text" id="productName" class="form-control" placeholder="Product Name" style="width: 100%; margin-bottom: 10px;">
                                <label for="productDescription" style="display: block; margin-bottom: 5px;">Description</label>
                                <textarea id="productDescription" class="form-control" placeholder="Product Description" style="width: 100%; margin-bottom: 10px;"></textarea>
                                <label for="productQuantity" style="display: block; margin-bottom: 5px;">Quantity</label>
                                <input type="number" id="productQuantity" class="form-control" placeholder="Quantity" style="width: 100%; margin-bottom: 10px;">
                                <label for="productPrice" style="display: block; margin-bottom: 5px;">Price</label>
                                <input type="number" id="productPrice" class="form-control" placeholder="Price" style="width: 100%; margin-bottom: 10px;">
                                <label for="productImage" style="display: block; margin-bottom: 5px;">Image</label>
                                <input type="file" id="productImage" class="form-control" accept=".jpg, .png" style="width: 100%;">
                            </form>
                        `,
                        confirmButtonText: 'Add',
                        preConfirm: () => {
                            const subcategoryId = document.getElementById('subcategoryId').value;
                            const name = document.getElementById('productName').value;
                            const description = document.getElementById('productDescription').value;
                            const quantity = document.getElementById('productQuantity').value;
                            const price = document.getElementById('productPrice').value;
                            const imageFile = document.getElementById('productImage').files[0];

                            // Validate fields
                            if (!subcategoryId || !name || !description || !quantity || !price || !imageFile) {
                                Swal.showValidationMessage('All fields are required');
                                return false;
                            }

                            // Validate image file
                            const validFormats = ['image/jpeg', 'image/png'];
                            if (!validFormats.includes(imageFile.type)) {
                                Swal.showValidationMessage('Only .jpg and .png files are allowed');
                                return false;
                            }
                            if (imageFile.size > 1 * 1024 * 1024) {
                                Swal.showValidationMessage('File size must not exceed 1 MB');
                                return false;
                            }

                            // Create FormData object
                            const formData = new FormData();
                            formData.append('subcategory_id', subcategoryId);
                            formData.append('name', name);
                            formData.append('description', description);
                            formData.append('quantity', quantity);
                            formData.append('price', price);
                            formData.append('image', imageFile);

                            return formData;
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch('http://smart-tech/API/products', {
                                method: 'POST',
                                body: result.value // Send FormData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 201) {
                                        Swal.fire('Success', 'Product added successfully', 'success');
                                        fetchProducts(); // Refresh products list
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Failed to add product', 'error');
                                });
                        }
                    });
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to fetch subcategories', 'error');
                });
        }



        // Add event listener for "Add Product" button
        const addProductBtn = document.getElementById('addProductBtn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', addProduct);
        }

        // Fetch products on page load
        fetchProducts();
    }
    // Delete product
    function deleteProduct(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`http://smart-tech/API/products/${id}`, {
                    method: 'DELETE',
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 200) {
                            Swal.fire('Deleted!', 'Product has been deleted.', 'success');
                            document.getElementById(`product-${id}`).remove();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to delete product', 'error');
                    });
            }
        });
    }

    // Edit product
    function editProduct(productId) {
        // Получаем детали товара по ID
        fetch(`http://smart-tech/API/products/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 200) {
                    Swal.fire('Error', 'Failed to fetch product details', 'error');
                    return;
                }

                const product = data.data;

                // Создаем модальное окно с предзаполненными значениями
                Swal.fire({
                    title: 'Edit Product',
                    html: `
                    <form id="editProductForm" style="text-align: left;">
                        <label for="editProductName" style="display: block; margin-bottom: 5px;">Product Name</label>
                        <input type="text" id="editProductName" class="form-control" value="${product.product_name}" style="width: 100%; margin-bottom: 10px;">

                        <label for="editProductDescription" style="display: block; margin-bottom: 5px;">Description</label>
                        <textarea id="editProductDescription" class="form-control" style="width: 100%; margin-bottom: 10px;">${product.description}</textarea>

                        <label for="editProductQuantity" style="display: block; margin-bottom: 5px;">Quantity</label>
                        <input type="number" id="editProductQuantity" class="form-control" value="${product.quantity}" style="width: 100%; margin-bottom: 10px;">

                        <label for="editProductPrice" style="display: block; margin-bottom: 5px;">Price</label>
                        <input type="number" id="editProductPrice" class="form-control" value="${product.price}" style="width: 100%; margin-bottom: 10px;">

                        <label for="editProductStatus" style="display: block; margin-bottom: 5px;">Status</label>
                        <select id="editProductStatus" class="form-control" style="width: 100%; margin-bottom: 10px;">
                            <option value="available" ${product.data_status === 'available' ? 'selected' : ''}>Available</option>
                            <option value="out_of_stock" ${product.data_status === 'out_of_stock' ? 'selected' : ''}>Out of Stock</option>
                        </select>

                        <label for="editProductImage" style="display: block; margin-bottom: 5px;">Current Image</label>
                        <img src="${product.image_url}" alt="${product.product_name}" style="display: block; width: 100px; height: 100px; margin-bottom: 10px;">

                        <label for="editProductImage" style="display: block; margin-bottom: 5px;">Change Image</label>
                        <input type="file" id="editProductImage" class="form-control" accept=".jpg, .png" style="width: 100%; margin-bottom: 10px;">
                    </form>
                `,
                    confirmButtonText: 'Save',
                    preConfirm: () => {
                        const name = document.getElementById('editProductName').value;
                        const description = document.getElementById('editProductDescription').value;
                        const quantity = document.getElementById('editProductQuantity').value;
                        const price = document.getElementById('editProductPrice').value;
                        const status = document.getElementById('editProductStatus').value;
                        const imageFile = document.getElementById('editProductImage').files[0];

                        if (!name || !description || !quantity || !price || !status) {
                            Swal.showValidationMessage('All fields are required');
                            return false;
                        }

                        return {
                            product_name: name,
                            description: description,
                            quantity: quantity,
                            price: price,
                            data_status: status,
                            imageFile: imageFile // Добавляем картинку
                        };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        const formData = new FormData();

                        formData.append('name', result.value.product_name);
                        formData.append('description', result.value.description);
                        formData.append('quantity', result.value.quantity);
                        formData.append('price', result.value.price);
                        formData.append('data_status', result.value.data_status);

                        if (result.value.imageFile) {
                            formData.append('image', result.value.imageFile);
                        }

                        fetch(`http://smart-tech/API/products/${productId}`, {
                            method: 'POST',
                            body: formData,
                        })
                            .then(async response => {
                                console.log('Response status:', response.status);
                                if (!response.ok) {
                                    const errorText = await response.text();
                                    throw new Error(`HTTP Error: ${response.status} - ${errorText}`);
                                }
                                const data = await response.json();
                                console.log('Response JSON:', data);

                                if (data.status === 200) {
                                    Swal.fire('Success', 'Product updated successfully', 'success');
                                    if (typeof fetchProducts === 'function') {
                                        fetchProducts(); // Обновление списка товаров
                                    } else {
                                        console.warn('fetchProducts is not defined');
                                    }
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Fetch error:', error);
                                Swal.fire('Error', 'Failed to update product', 'error');
                            });

                    }
                });
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to fetch product details', 'error');
            });
    }





    // Load default page
    loadPage('category');
</script>
</body>
</html>

<style>
    /* Category-specific styles */
    #categoriesContent h1 {
        color: #007bff;
    }

</style>
<div class="container mt-5" id="categoriesContent">
    <h1 class="text-center">Manage Categories</h1>

    <!-- Button to Add Category -->
    <button class="btn btn-primary mb-3" id="addCategoryBtn"><i class="fas fa-folder-plus"></i> Add Category</button>

    <!-- Categories Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Date Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="categoriesTableBody">
        <!-- Category rows will be dynamically inserted here -->
        </tbody>
    </table>
</div>

<script>
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

</script>
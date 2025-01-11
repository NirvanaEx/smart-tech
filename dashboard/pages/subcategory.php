<style>
    /* Subcategory-specific styles */
    #subcategoriesContent h1 {
        color: #28a745;
    }
</style>
<div class="container mt-5" id="subcategoriesContent">
    <h1 class="text-center">Manage Subcategories</h1>

    <!-- Button to Add Subcategory -->
    <button class="btn btn-primary mb-3" id="addSubcategoryBtn"><i class="fas fa-folder-plus"></i> Add Subcategory</button>

    <!-- Subcategories Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Category</th>
            <th>Subcategory Name</th>
            <th>Date Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="subcategoriesTableBody">
        <!-- Subcategory rows will be dynamically inserted here -->
        </tbody>
    </table>
</div>
<script>

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


</script>

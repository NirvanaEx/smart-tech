<div class="container mt-5" id="productsContent">
    <h1 class="text-center">Manage Products</h1>

    <!-- Button to Add Product -->
    <button class="btn btn-primary mb-3" id="addProductBtn"><i class="fas fa-box"></i> Add Product</button>

    <!-- Products Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Category</th>
            <th>Subcategory</th>
            <th>Price</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="productsTableBody">
        <!-- Product rows will be dynamically inserted here -->
        </tbody>
    </table>
</div>

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
        const content = document.getElementById('content');
        content.innerHTML = '<div class="text-center text-light">Loading...</div>';

        fetch(`pages/${page}.php`)
            .then(response => {
                if (!response.ok) throw new Error(`Failed to load page: ${response.statusText}`);
                return response.text();
            })
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;

                // Replace content with HTML from tempDiv
                content.innerHTML = tempDiv.innerHTML;

                // Extract and execute scripts
                const scripts = tempDiv.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript);
                });
            })
            .catch(error => {
                content.innerHTML = `<div class="text-danger text-center">Error: ${error.message}</div>`;
            });
    }

    // Load default page
    loadPage('category');
</script>
</body>
</html>

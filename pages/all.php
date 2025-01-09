

<div class="container my-4">
    <h2 class="text-light">Все товары</h2>
    <div class="row g-4">
        <?php
        // Пример данных о товарах (можно заменить на данные из базы данных)
        $products = [
            ["id" => 1, "name" => "Телевизор 4K", "price" => "29 990 ₽", "image" => "upload/product1.jpg"],
            ["id" => 2, "name" => "Смартфон", "price" => "19 990 ₽", "image" => "upload/product2.jpg"],
            ["id" => 3, "name" => "Ноутбук", "price" => "49 990 ₽", "image" => null],
            ["id" => 4, "name" => "Игровая консоль", "price" => "39 990 ₽", "image" => "upload/product4.jpg"],
            ["id" => 5, "name" => "Наушники", "price" => "9 990 ₽", "image" => ""],
        ];

        foreach ($products as $product):
            $image = $product['image'] && file_exists($product['image'])
                ? $product['image']
                : "upload/no_image_placeholder.jpg";
            ?>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="card bg-dark text-light h-100">
                    <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text"><?= $product['price'] ?></p>
                        <!-- Кнопки действий -->
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <button class="btn btn-outline-light btn-cart w-100 me-2">
                                <i class="fas fa-cart-plus"></i> В корзинку
                            </button>

                            <button class="btn btn-outline-light btn-favorite mx-2">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="btn btn-outline-light">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

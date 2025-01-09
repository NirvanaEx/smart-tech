<div class="row row-cols-1 row-cols-md-4 g-4">
    <?php for ($i = 1; $i <= 12; $i++): ?>
        <div class="col">
            <div class="card bg-secondary text-light">
                <img src="upload/no_image_placeholder.jpg" class="card-img-top" alt="Телефон <?= $i ?>">
                <div class="card-body">
                    <h5 class="card-title">Телефон <?= $i ?></h5>
                    <p class="card-text">Описание телефона</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>19 990₽</span>
                        <button class="btn btn-dark"><i class="fas fa-shopping-bag"></i> Купить</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>

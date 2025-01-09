<div class="swiper">
    <div class="swiper-wrapper">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <div class="swiper-slide">
                <div class="card bg-secondary text-light">
                    <img src="upload/no_image_placeholder.jpg" class="card-img-top" alt="Продукт <?= $i ?>">
                    <div class="card-body">
                        <h5 class="card-title">Продукт <?= $i ?></h5>
                        <p class="card-text">Описание новинки</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>1 990₽</span>
                            <button class="btn btn-dark"><i class="fas fa-shopping-bag"></i> Купить</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <!-- Навигация -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <!-- Пагинация -->
    <div class="swiper-pagination"></div>
</div>
<script>

</script>


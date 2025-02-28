<style>
    /* Swiper стили */
    .swiper {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }
    .swiper-wrapper {
        display: flex;
    }
    .swiper-slide {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
        width: auto;
    }
    .swiper-slide .card {
        border-radius: 10px;
    }
    .swiper-slide img {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
    }
    .swiper-button-prev,
    .swiper-button-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: auto;
        height: auto;
        z-index: 10;
        color: #fff;
    }
    .swiper-button-prev {
        left: -30px;
    }
    .swiper-button-next {
        right: -30px;
    }
    .swiper-pagination {
        position: relative;
        margin-top: 10px;
    }
    .swiper-pagination-bullet {
        background: #d3d3d3;
        opacity: 0.8;
    }
    .swiper-pagination-bullet-active {
        background: #fff;
        opacity: 1;
    }
</style>

<div class="swiper">
    <div class="swiper-wrapper" id="newProductsWrapper">
        <!-- Слайды будут загружены через JS -->
    </div>
    <!-- Стрелки навигации -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <!-- Пагинация -->
    <div class="swiper-pagination"></div>
</div>



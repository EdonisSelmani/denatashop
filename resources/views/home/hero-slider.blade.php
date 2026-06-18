<section class="relative overflow-hidden">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="relative h-[400px] md:h-[500px] lg:h-[600px] bg-gradient-to-r from-blue-600 to-purple-600">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="relative h-full flex items-center">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-white">
                            <div class="max-w-2xl">
                                <h1 class="text-4xl md:text-6xl font-bold mb-4">Oferta Speciale</h1>
                                <p class="text-lg md:text-xl mb-8">Deri në 50% zbritje për produktet e zgjedhura</p>
                                <a href="{{ route('shop') }}" class="inline-block bg-white text-gray-900 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                                    Shporto Tani
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="swiper-slide">
                <div class="relative h-[400px] md:h-[500px] lg:h-[600px] bg-gradient-to-r from-purple-600 to-pink-600">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="relative h-full flex items-center">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-white">
                            <div class="max-w-2xl">
                                <h1 class="text-4xl md:text-6xl font-bold mb-4">Produkte të Reja</h1>
                                <p class="text-lg md:text-xl mb-8">Zbuloni koleksionin më të ri</p>
                                <a href="{{ route('shop') }}" class="inline-block bg-white text-gray-900 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                                    Shiko Produktet
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<script>
    const heroSwiper = new Swiper('.heroSwiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        }
    });
</script>
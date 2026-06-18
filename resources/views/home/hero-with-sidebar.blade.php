{{-- resources/views/home/hero-with-sidebar.blade.php --}}
<section class="bg-white py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left Sidebar - Categories -->
            <div class="lg:w-1/4">
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            Të gjitha kategoritë
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-[400px] overflow-y-auto">
                        @foreach($categories as $category)
                            <a href="{{ route('category.show', $category->slug) }}" 
                               class="sidebar-category-item flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition group">
                                <div class="flex items-center gap-3">
                                    @if($category->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-6 h-6 object-contain">
                                    @else
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    @endif
                                    <span class="text-gray-700 group-hover:text-blue-600 transition font-medium">{{ $category->name }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                @if($category->subcategories->count() > 0)
                                    <div class="hidden lg:block absolute left-full top-0 ml-1 w-64 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <div class="p-3">
                                            <div class="font-semibold text-gray-800 px-3 py-2 bg-gray-50 rounded-lg mb-2">
                                                {{ $category->name }}
                                            </div>
                                            @foreach($category->subcategories as $subcategory)
                                                <a href="{{ route('shop', ['subcategory' => $subcategory->slug]) }}" 
                                                   class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition">
                                                    {{ $subcategory->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Hero Slider -->
            <div class="lg:w-3/4">
                <div class="swiper heroSwiper rounded-xl overflow-hidden shadow-lg">
                    <div class="swiper-wrapper">
                        <!-- Slide 1 -->
                        <div class="swiper-slide">
                            <div class="relative h-[320px] md:h-[400px] bg-gradient-to-r from-blue-600 to-indigo-600">
                                <div class="absolute inset-0 bg-black/20"></div>
                                <div class="relative h-full flex items-center px-8 md:px-12">
                                    <div class="text-white max-w-lg">
                                        <h2 class="text-3xl md:text-4xl font-bold mb-3">Oferta Speciale</h2>
                                        <p class="text-lg mb-6 opacity-90">Deri në 50% zbritje për produktet e zgjedhura</p>
                                        <a href="{{ route('shop') }}" class="inline-block bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                                            Shporto Tani →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 2 -->
                        <div class="swiper-slide">
                            <div class="relative h-[320px] md:h-[400px] bg-gradient-to-r from-purple-600 to-pink-600">
                                <div class="absolute inset-0 bg-black/20"></div>
                                <div class="relative h-full flex items-center px-8 md:px-12">
                                    <div class="text-white max-w-lg">
                                        <h2 class="text-3xl md:text-4xl font-bold mb-3">Produkte të Reja</h2>
                                        <p class="text-lg mb-6 opacity-90">Zbuloni koleksionin më të ri</p>
                                        <a href="{{ route('shop', ['sort' => 'latest']) }}" class="inline-block bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                                            Shiko Produktet →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 3 -->
                        <div class="swiper-slide">
                            <div class="relative h-[320px] md:h-[400px] bg-gradient-to-r from-orange-500 to-red-500">
                                <div class="absolute inset-0 bg-black/20"></div>
                                <div class="relative h-full flex items-center px-8 md:px-12">
                                    <div class="text-white max-w-lg">
                                        <h2 class="text-3xl md:text-4xl font-bold mb-3">Flash Sale</h2>
                                        <p class="text-lg mb-6 opacity-90">Zbritje të shpejta për 24 orët e ardhshme</p>
                                        <a href="{{ route('shop') }}" class="inline-block bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                                            Bleni Tani →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
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
        fadeEffect: { crossFade: true }
    });
</script>
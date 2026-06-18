{{-- resources/views/home/product-sections.blade.php --}}
<!-- Featured Products -->
<section class="py-10 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Produktet e Veçuara</h2>
                <p class="text-sm text-gray-500 mt-1">Zgjidhjet më të mira për ju</p>
            </div>
            <a href="{{ route('shop', ['featured' => 1]) }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1">
                Shiko të gjitha
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @foreach($featuredProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>

<!-- Promotional Banner -->
<section class="py-8 bg-gradient-to-r from-blue-600 to-indigo-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center text-white">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h3 class="text-2xl md:text-3xl font-bold mb-2">Oferta Speciale të Fundjavës</h3>
                <p class="text-base opacity-90">Zbritje deri në 40% për produkte të zgjedhura</p>
            </div>
            <a href="{{ route('shop') }}" class="bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                Shporto Tani
            </a>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="py-10 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Produktet e Reja</h2>
                <p class="text-sm text-gray-500 mt-1">Mbërritjet më të fundit</p>
            </div>
            <a href="{{ route('shop', ['sort' => 'latest']) }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1">
                Shiko të gjitha
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @foreach($newProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>

<!-- Discount Products with Timer -->
<section class="py-10 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl p-6 mb-8">
            <div class="flex flex-col lg:flex-row justify-between items-center">
                <div class="text-white mb-4 lg:mb-0">
                    <h2 class="text-2xl md:text-3xl font-bold mb-1 flex items-center gap-2">
                        🔥 Flash Sale
                    </h2>
                    <p class="text-sm opacity-90">Zbritje të shpejta për produkte të zgjedhura</p>
                </div>
                <div class="flex gap-3 text-center">
                    <div class="bg-white/20 rounded-xl px-4 py-2 backdrop-blur">
                        <div class="text-2xl font-bold text-white" id="hours">00</div>
                        <div class="text-xs text-white">Orë</div>
                    </div>
                    <div class="bg-white/20 rounded-xl px-4 py-2 backdrop-blur">
                        <div class="text-2xl font-bold text-white" id="minutes">00</div>
                        <div class="text-xs text-white">Minuta</div>
                    </div>
                    <div class="bg-white/20 rounded-xl px-4 py-2 backdrop-blur">
                        <div class="text-2xl font-bold text-white" id="seconds">00</div>
                        <div class="text-xs text-white">Sekonda</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @foreach($discountProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>

<script>
    function startCountdown() {
        const targetDate = new Date();
        targetDate.setHours(targetDate.getHours() + 24);
        
        function updateCountdown() {
            const now = new Date();
            const diff = targetDate - now;
            
            if (diff <= 0) {
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (3600000)) / (1000 * 60));
            const seconds = Math.floor((diff % (60000)) / 1000);
            
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
    
    startCountdown();
</script>
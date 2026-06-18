{{-- resources/views/home/promo-banners.blade.php --}}
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-r from-green-500 to-teal-500 rounded-2xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-2">Dorëzim Falas</h3>
                <p class="mb-4 opacity-90">Për të gjitha porositë mbi 50€</p>
                <a href="{{ route('shop') }}" class="inline-block bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
                    Shporto Tani →
                </a>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-2">Anëtarësimi VIP</h3>
                <p class="mb-4 opacity-90">Zbritje speciale për anëtarët tanë</p>
                <a href="{{ route('register') }}" class="inline-block bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
                    Regjistrohu Tani →
                </a>
            </div>
        </div>
    </div>
</section>
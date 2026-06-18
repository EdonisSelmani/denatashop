{{-- resources/views/layouts/top-bar.blade.php --}}
<div class="bg-gray-900 text-white text-sm py-2.5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-6">
                <span class="text-gray-300">Blerje të sigurta</span>
                <span class="text-gray-300">Dërgesa të shpejta kudo në Kosovë</span>
                <span class="text-gray-300">Mbi 100,000 produkte originale</span>
            </div>
            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:text-blue-400 transition">Hyni</a>
                    <a href="{{ route('register') }}" class="hover:text-blue-400 transition">Regjistrohu</a>
                @else
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 hover:text-blue-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profili im</a>
                            <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Lista e dëshirave</a>
                            <a href="{{ route('cart.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Shporta</a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Porosite e mia</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Dilni</button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</div>

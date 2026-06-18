<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Denataa Shop
                </a>
            </div>

            <div class="hidden md:flex flex-1 max-w-xl mx-8">
                <form action="{{ route('shop') }}" method="GET" class="w-full">
                    <div class="relative">
                        <input type="text"
                               name="search"
                               placeholder="Kerkoni produkte..."
                               class="w-full px-5 py-3 rounded-full border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition"
                               value="{{ request('search') }}">
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex items-center space-x-8">
                <a href="{{ route('wishlist.index') }}" class="relative text-gray-700 hover:text-red-500 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span id="wishlist-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {{ $wishlistCount ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-blue-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
                    </svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

                <button @click="open = !open" class="md:hidden text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="open" x-cloak class="md:hidden py-4 border-t border-gray-200">
            <form action="{{ route('shop') }}" method="GET" class="mb-4">
                <div class="relative">
                    <input type="text" name="search" placeholder="Kerkoni produkte..." class="w-full px-4 py-2 rounded-lg border border-gray-300">
                    <button type="submit" class="absolute right-2 top-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>
            @foreach($categories ?? [] as $category)
                <a href="{{ route('shop', ['category' => $category->slug]) }}" class="block py-2 text-gray-700">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</nav>

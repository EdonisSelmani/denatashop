<footer class="bg-gray-900 text-white pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <div>
                <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent mb-4">
                    Denataa Shop
                </h3>
                <p class="text-gray-400 mb-4">Dyqan online per produkte te zgjedhura dhe dergese te shpejte.</p>
            </div>

            <div>
                <h4 class="font-semibold text-lg mb-4">Lidhje te shpejta</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('shop') }}" class="text-gray-400 hover:text-white transition">Te gjitha produktet</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'latest']) }}" class="text-gray-400 hover:text-white transition">Produktet e reja</a></li>
                    <li><a href="{{ route('cart.index') }}" class="text-gray-400 hover:text-white transition">Shporta</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-lg mb-4">Kategorite</h4>
                <ul class="space-y-2">
                    @foreach(($categories ?? collect())->take(5) as $category)
                        <li>
                            <a href="{{ route('category.show', $category->slug) }}" class="text-gray-400 hover:text-white transition">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-lg mb-4">Kontakt</h4>
                <p class="text-gray-400">Per porosi dhe pyetje, na kontaktoni ne kanalet zyrtare te dyqanit.</p>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-6 text-center text-gray-400">
            <p>&copy; {{ date('Y') }} Denataa Shop. Te gjitha te drejtat e rezervuara.</p>
        </div>
    </div>
</footer>

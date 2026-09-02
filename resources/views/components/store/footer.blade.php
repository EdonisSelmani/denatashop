<footer class="border-t border-[#2A2D31] bg-[#15181B] text-[#F7F5F1]">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[1.25fr_0.75fr_0.75fr_0.9fr]">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" aria-label="Denata Shop">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-md bg-white">
                        <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="h-11 w-auto object-contain">
                    </span>
                    <span class="leading-none">
                        <span class="block text-lg font-black uppercase text-white">Denata</span>
                        <span class="block text-xs font-bold uppercase text-[#D7B16D]">Shop</span>
                    </span>
                </a>
                <p class="mt-5 max-w-sm text-sm leading-7 text-[#D8D1C6]">
                    Denata Shop sjell produkte te zgjedhura per instalime sanitare, vegla pune, kopsht dhe elektrike, me fokus ne cilesi dhe sherbim te besueshem.
                </p>
            </div>

            <div>
                <h2 class="text-sm font-black uppercase text-[#B88A3B]">Lidhje</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="{{ route('home') }}" class="text-[#D8D1C6] transition hover:text-white">Ballina</a></li>
                    <li><a href="{{ route('shop') }}" class="text-[#D8D1C6] transition hover:text-white">Të gjitha produktet</a></li>
                    <li><a href="{{ route('sitemap') }}" class="text-[#D8D1C6] transition hover:text-white">Sitemap</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-black uppercase text-[#B88A3B]">Kategorite</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach(($categories ?? collect())->take(6) as $category)
                        <li>
                            <a href="{{ route('category.show', $category->slug) }}" class="text-[#D8D1C6] transition hover:text-white">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-black uppercase text-[#B88A3B]">Kontakt</h2>
                <div class="mt-4 space-y-3 text-sm leading-6 text-[#D8D1C6]">
                    <p>Per porosi, disponueshmeri dhe pyetje rreth produkteve, na kontaktoni ne kanalet zyrtare te dyqanit.</p>
                    <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 font-bold text-white transition hover:text-[#D7B16D]">
                        Shiko katalogun
                        <x-store.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-[#D8D1C6] sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Denata Shop. Te gjitha te drejtat e rezervuara.</p>
            <p class="text-[#B88A3B]">Per shtepi, pune dhe kopsht.</p>
        </div>
    </div>
</footer>

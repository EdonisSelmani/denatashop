@php
    $categoryCollection = collect($categories ?? []);
    $lowerName = fn ($model) => \Illuminate\Support\Str::of($model?->name ?? '')->ascii()->lower();
    $findCategory = function (array $needles) use ($categoryCollection, $lowerName) {
        return $categoryCollection->first(function ($category) use ($needles, $lowerName) {
            return collect($needles)->contains(fn ($needle) => $lowerName($category)->contains($needle));
        });
    };

    $sanitaryCategory = $findCategory(['tusha', 'sanitari', 'ujesjelles']);
    $toolsCategory = $findCategory(['vegla pune']);
    $gardenCategory = $findCategory(['vegla kopshti']);
    $electricCategory = $findCategory(['elektr', 'elektronike']);
    $batteryParent = $categoryCollection->first(fn ($category) => $category->subcategories->contains(fn ($subcategory) => $lowerName($subcategory)->contains('bateria')));
    $batterySubcategory = $batteryParent?->subcategories->first(fn ($subcategory) => $lowerName($subcategory)->contains('bateria'));

    $makeCategoryItem = fn ($label, $icon, $category) => [
        'label' => $label,
        'icon' => $icon,
        'href' => $category ? route('category.show', $category->slug) : route('shop'),
        'category_slug' => $category?->slug,
        'children' => $category?->subcategories ?? collect(),
    ];

    $navItems = collect([
        $makeCategoryItem('Sanitari', 'tap', $sanitaryCategory),
        $makeCategoryItem('Vegla Pune', 'wrench', $toolsCategory),
        $makeCategoryItem('Vegla Kopshti', 'leaf', $gardenCategory),
        $makeCategoryItem('Elektrike', 'bolt', $electricCategory),
        [
            'label' => 'Bateri',
            'icon' => 'battery',
            'href' => $batterySubcategory
                ? route('shop', ['category' => $batteryParent?->slug, 'subcategory' => $batterySubcategory->slug])
                : route('shop', ['search' => 'bateri']),
            'category_slug' => $batteryParent?->slug,
            'children' => collect(),
        ],
    ]);
@endphp

<header
    x-data="{ mobileOpen: false, accountOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', mobileOpen)"
    @keydown.escape.window="mobileOpen = false"
    class="sticky top-0 z-40 border-b border-[#E5E1DA] bg-white/95 shadow-[0_1px_0_rgba(21,24,27,0.04)] backdrop-blur"
>
    <div class="bg-[#15181B] text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-center gap-5 px-4 py-2 text-[11px] font-semibold uppercase sm:justify-between sm:px-6 lg:px-8">
            <div class="hidden items-center gap-5 sm:flex">
                <span class="inline-flex items-center gap-2"><x-store.icon name="truck" class="h-4 w-4 text-[#B88A3B]" /> Dergesa ne gjithe Kosoven</span>
                <span class="inline-flex items-center gap-2"><x-store.icon name="lock" class="h-4 w-4 text-[#B88A3B]" /> Pagese e sigurt</span>
                <span class="inline-flex items-center gap-2"><x-store.icon name="headset" class="h-4 w-4 text-[#B88A3B]" /> Mbeshtetje per porosi</span>
            </div>
            <div class="flex items-center gap-4">
                @guest
                    <a href="{{ route('login') }}" class="transition hover:text-[#D7B16D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">Hyni</a>
                    <a href="{{ route('register') }}" class="transition hover:text-[#D7B16D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">Regjistrohu</a>
                @else
                    <span class="hidden sm:inline">Pershendetje, {{ Str::limit(Auth::user()->name, 18) }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="transition hover:text-[#D7B16D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">Dilni</button>
                    </form>
                @endguest
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[74px] items-center gap-4">
            <a href="{{ route('home') }}" class="shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" aria-label="Denata Shop">
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="h-[42px] w-auto object-contain sm:h-[58px]">
            </a>

            <x-store.search-autocomplete class="hidden flex-1 md:block" />

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                @auth
                    <div class="relative hidden md:block">
                        <button type="button" @click="accountOpen = !accountOpen" @keydown.escape="accountOpen = false" class="inline-flex h-11 items-center gap-2 rounded-md border border-[#E5E1DA] px-3 text-sm font-bold text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" :aria-expanded="accountOpen.toString()">
                            <x-store.icon name="user" class="h-5 w-5" />
                            Llogaria
                            <x-store.icon name="chevron-down" class="h-4 w-4" />
                        </button>
                        <div x-show="accountOpen" x-cloak @click.outside="accountOpen = false" class="absolute right-0 mt-2 w-56 rounded-lg border border-[#E5E1DA] bg-white py-2 shadow-xl">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm font-semibold text-[#22272B] hover:bg-[#F7F5F1]">Profili im</a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm font-semibold text-[#22272B] hover:bg-[#F7F5F1]">Porosite</a>
                            @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm font-semibold text-[#9A712E] hover:bg-[#F7F5F1]">Admin</a>
                            @endif
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden h-11 items-center gap-2 rounded-md border border-[#E5E1DA] px-3 text-sm font-bold text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#9A712E] md:inline-flex">
                        <x-store.icon name="user" class="h-5 w-5" />
                        Hyrja
                    </a>
                @endauth

                <a href="{{ route('wishlist.index') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-md border border-[#E5E1DA] text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" aria-label="Lista e deshirave">
                    <x-store.icon name="heart" class="h-5 w-5" />
                    <span id="wishlist-count" class="absolute -right-2 -top-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#C9473D] px-1 text-[11px] font-bold text-white">{{ $wishlistCount ?? 0 }}</span>
                </a>

                <a href="{{ route('cart.index') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-md bg-[#15181B] text-white transition hover:bg-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" aria-label="Shporta">
                    <x-store.icon name="cart" class="h-5 w-5" />
                    <span id="cart-count" class="absolute -right-2 -top-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#B88A3B] px-1 text-[11px] font-bold text-white">{{ $cartCount ?? 0 }}</span>
                </a>

                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-[#E5E1DA] text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B] lg:hidden" @click="mobileOpen = true" aria-label="Hap menune">
                    <x-store.icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <nav class="hidden items-center gap-1 border-t border-[#E5E1DA] py-2 lg:flex" aria-label="Kategorite kryesore">
            @foreach($navItems as $item)
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <a href="{{ $item['href'] }}"
                       @focus="open = true"
                       class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-bold text-[#22272B] transition hover:bg-[#F7F5F1] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
                        <x-store.icon :name="$item['icon']" class="h-4 w-4 text-[#B88A3B]" />
                        {{ $item['label'] }}
                        @if(($item['children'] ?? collect())->count())
                            <x-store.icon name="chevron-down" class="h-3.5 w-3.5" />
                        @endif
                    </a>
                    @if(($item['children'] ?? collect())->count())
                        <div x-show="open" x-cloak x-transition class="absolute left-0 top-full z-50 mt-2 w-72 rounded-lg border border-[#E5E1DA] bg-white p-3 shadow-xl" @keydown.escape.window="open = false">
                            <div class="grid max-h-[420px] gap-1 overflow-y-auto">
                                @foreach($item['children']->take(18) as $subcategory)
                                    <a href="{{ route('shop', ['category' => $item['category_slug'], 'subcategory' => $subcategory->slug]) }}" class="rounded-md px-3 py-2 text-sm font-semibold text-[#6B6F74] transition hover:bg-[#F7F5F1] hover:text-[#9A712E]">
                                        {{ $subcategory->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>

    <x-store.mobile-menu :nav-items="$navItems" />
</header>

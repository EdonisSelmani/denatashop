@php
    $categoryCollection = collect($categories ?? []);
    $lowerName = fn ($model) => \Illuminate\Support\Str::of($model?->name ?? '')->ascii()->lower();
    $findCategory = function (array $needles) use ($categoryCollection, $lowerName) {
        return $categoryCollection->first(function ($category) use ($needles, $lowerName) {
            return collect($needles)->contains(fn ($needle) => $lowerName($category)->contains($needle));
        });
    };

    $sanitaryCategory = $findCategory(['tusha', 'sanitari']);
    $toolsCategory = $findCategory(['vegla pune']);
    $gardenCategory = $findCategory(['vegla kopshti']);
    $electricCategory = $findCategory(['elektr', 'elektronike']);
    $plumbingCategory = $findCategory(['ujesjelles']);

    $makeCategoryItem = fn ($label, $icon, $category, $fallbackSearch = null) => [
        'label' => $label,
        'icon' => $icon,
        'href' => $category ? route('category.show', $category->slug) : route('shop', $fallbackSearch ? ['search' => $fallbackSearch] : []),
        'category_slug' => $category?->slug,
        'children' => $category?->subcategories ?? collect(),
    ];

    $navItems = collect([
        $makeCategoryItem('Sanitari', 'tap', $sanitaryCategory, 'sanitari'),
        $makeCategoryItem('Vegla Pune', 'wrench', $toolsCategory, 'vegla pune'),
        $makeCategoryItem('Vegla Kopshti', 'leaf', $gardenCategory, 'vegla kopshti'),
        $makeCategoryItem('Elektronike', 'bolt', $electricCategory, 'elektronike'),
        $makeCategoryItem('Ujësjellës', 'tap', $plumbingCategory, 'ujesjelles'),
    ]);

    $categoryMenuItems = $categoryCollection
        ->filter(fn ($category) => ($category->active_products_count ?? 0) > 0 || $category->subcategories->count())
        ->take(12)
        ->values();
@endphp

<header
    x-data="{ mobileOpen: false, accountOpen: false, categoryOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', mobileOpen)"
    @keydown.escape.window="mobileOpen = false; categoryOpen = false; accountOpen = false"
    class="sticky top-0 z-40 border-b border-[#E5E7EB] bg-white shadow-[0_10px_26px_rgba(17,17,17,0.05)]"
>
    <div class="bg-[#111111] text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 px-4 py-2 text-sm font-semibold sm:px-6 lg:px-8">
            <x-store.icon name="truck" class="h-4 w-4 text-[#C9A14A]" />
            <span>Transport i shpejtë në të gjithë Kosovën</span>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[66px] items-center gap-3 md:min-h-[104px] lg:gap-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A] lg:w-[300px]" aria-label="Denata Shop">
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="h-11 w-auto object-contain sm:h-14 lg:h-[72px]">
                <span class="hidden min-w-0 leading-none sm:block">
                    <span class="block whitespace-nowrap text-xl font-black uppercase text-[#111111] lg:text-2xl">
                        Denata <span class="text-[#9A712E]">Shop</span>
                    </span>
                    <span class="mt-1 block text-xs font-semibold text-[#6B7280]">Për shtëpi, punë dhe kopsht</span>
                </span>
            </a>

            <x-store.search-autocomplete
                id="desktop-store-search"
                class="hidden flex-1 md:block"
                box-class="relative mx-auto max-w-[620px]"
            />

            <div class="ml-auto flex items-center gap-1 sm:gap-2 lg:min-w-[300px] lg:justify-end">
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E5E7EB] text-[#111111] transition hover:border-[#C9A14A] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A] md:hidden" @click="mobileOpen = true; $nextTick(() => document.getElementById('mobile-store-search')?.focus())" aria-label="Kërko produkte">
                    <x-store.icon name="search" class="h-5 w-5" />
                </button>

                <a href="{{ route('wishlist.index') }}" class="group relative hidden min-w-[86px] flex-col items-center justify-center gap-1 rounded-md px-2 py-2 text-center text-[#111111] transition hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A] sm:flex" aria-label="Të preferuarat">
                    <span class="relative inline-flex">
                        <x-store.icon name="heart" class="h-7 w-7" />
                        <span id="wishlist-count" class="absolute -right-3 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#C9A14A] px-1 text-[11px] font-black text-white">{{ $wishlistCount ?? 0 }}</span>
                    </span>
                    <span class="hidden text-xs font-bold text-[#111111] group-hover:text-[#9A712E] xl:block">Të preferuarat</span>
                </a>

                <a href="{{ route('cart.index') }}" class="group relative inline-flex min-w-10 flex-col items-center justify-center gap-1 rounded-md px-1 py-2 text-center text-[#111111] transition hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A] sm:min-w-[86px] sm:px-2" aria-label="Shporta ime">
                    <span class="relative inline-flex">
                        <x-store.icon name="cart" class="h-7 w-7" />
                        <span id="cart-count" class="absolute -right-3 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#C9A14A] px-1 text-[11px] font-black text-white">{{ $cartCount ?? 0 }}</span>
                    </span>
                    <span class="hidden text-xs font-bold text-[#111111] group-hover:text-[#9A712E] xl:block">Shporta ime</span>
                </a>

                @auth
                    <div class="relative hidden md:block">
                        <button type="button" @click="accountOpen = !accountOpen" class="group inline-flex min-w-[92px] flex-col items-center justify-center gap-1 rounded-md px-2 py-2 text-center text-[#111111] transition hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]" :aria-expanded="accountOpen.toString()">
                            <x-store.icon name="user" class="h-7 w-7" />
                            <span class="hidden text-xs font-bold text-[#111111] group-hover:text-[#9A712E] xl:block">Llogaria</span>
                        </button>
                        <div x-show="accountOpen" x-cloak @click.outside="accountOpen = false" class="absolute right-0 mt-2 w-56 rounded-lg border border-[#E5E7EB] bg-white py-2 shadow-xl">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm font-semibold text-[#111111] hover:bg-[#F7F6F3]">Profili im</a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm font-semibold text-[#111111] hover:bg-[#F7F6F3]">Porositë</a>
                            @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm font-semibold text-[#9A712E] hover:bg-[#F7F6F3]">Admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-[#E5E7EB] pt-2">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#111111] hover:bg-[#F7F6F3]">Dilni</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="group hidden min-w-[108px] flex-col items-center justify-center gap-1 rounded-md px-2 py-2 text-center text-[#111111] transition hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A] md:flex" aria-label="Hyr ose regjistrohu">
                        <x-store.icon name="user" class="h-7 w-7" />
                        <span class="hidden text-xs font-bold text-[#111111] group-hover:text-[#9A712E] xl:block">Hyr / Regjistrohu</span>
                    </a>
                @endauth

                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E5E7EB] text-[#111111] transition hover:border-[#C9A14A] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A] lg:hidden" @click="mobileOpen = true" aria-label="Hap menunë">
                    <x-store.icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <nav class="hidden items-center gap-8 border-t border-[#E5E7EB] py-3 lg:flex" aria-label="Navigimi kryesor">
            <div class="relative" @mouseenter="categoryOpen = true" @mouseleave="categoryOpen = false">
                <button type="button"
                        @click="categoryOpen = !categoryOpen"
                        class="inline-flex h-12 min-w-[220px] items-center justify-between gap-4 rounded-md bg-[#111111] px-5 text-base font-black text-white shadow-[0_12px_26px_rgba(17,17,17,0.12)] transition hover:bg-[#C9A14A] hover:text-[#111111] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]"
                        :aria-expanded="categoryOpen.toString()">
                    <span class="inline-flex items-center gap-3">
                        <x-store.icon name="menu" class="h-5 w-5" />
                        Kategoritë
                    </span>
                    <x-store.icon name="chevron-down" class="h-4 w-4" />
                </button>

                <div x-show="categoryOpen" x-cloak x-transition class="absolute left-0 top-full z-50 mt-3 w-[340px] rounded-lg border border-[#E5E7EB] bg-white p-3 shadow-2xl" @keydown.escape.window="categoryOpen = false">
                    <div class="grid gap-1">
                        @forelse($categoryMenuItems as $category)
                            <a href="{{ route('category.show', $category->slug) }}" class="flex items-center justify-between rounded-md px-3 py-2.5 text-sm font-bold text-[#111111] transition hover:bg-[#F7F6F3] hover:text-[#9A712E]">
                                <span>{{ $category->name }}</span>
                                <span class="text-xs font-semibold text-[#6B7280]">{{ $category->active_products_count ?? 0 }}</span>
                            </a>
                        @empty
                            <a href="{{ route('shop') }}" class="rounded-md px-3 py-2.5 text-sm font-bold text-[#111111] transition hover:bg-[#F7F6F3]">Të gjitha produktet</a>
                        @endforelse
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="relative py-3 text-base font-semibold text-[#9A712E]">
                Ballina
                <span class="absolute inset-x-0 -bottom-3 h-0.5 bg-[#C9A14A]"></span>
            </a>

            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}" class="py-3 text-base font-semibold text-[#111111] transition hover:text-[#9A712E]">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <x-store.mobile-menu :nav-items="$navItems" />
</header>

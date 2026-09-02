@props(['navItems' => collect()])

<div
    x-cloak
    class="fixed inset-0 z-50 lg:hidden"
    :class="mobileOpen ? 'pointer-events-auto' : 'pointer-events-none'"
    :aria-hidden="(!mobileOpen).toString()"
    aria-modal="true"
    role="dialog"
>
    <div
        x-show="mobileOpen"
        x-transition.opacity
        class="absolute inset-0 bg-[#15181B]/65 backdrop-blur-sm"
        @click="mobileOpen = false"
    ></div>

    <aside
           x-show="mobileOpen"
           class="absolute right-0 top-0 flex h-[100dvh] w-[90vw] max-w-[420px] flex-col overflow-y-auto overflow-x-hidden bg-[#F7F5F1] shadow-2xl"
           style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full">
        <div class="flex items-center justify-between border-b border-[#E5E1DA] bg-white px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" aria-label="Denata Shop">
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="h-10 w-auto object-contain">
                <span class="leading-none">
                    <span class="block text-sm font-black uppercase text-[#15181B]">Denata</span>
                    <span class="block text-[10px] font-bold uppercase text-[#9A712E]">Shop</span>
                </span>
            </a>
            <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E5E1DA] text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]"
                    @click="mobileOpen = false"
                    aria-label="Mbyll menune">
                <x-store.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <div class="space-y-4 px-4 py-4">
            <x-store.search-autocomplete
                id="mobile-store-search"
                placeholder="Kerko produkte ose SKU"
                box-class="relative"
                input-class="w-full rounded-md border border-[#E5E1DA] bg-white py-3 pl-4 pr-12 text-sm text-[#17191C] placeholder:text-[#6B6F74] focus:border-[#B88A3B] focus:ring-[#B88A3B]"
                button-class="absolute right-1.5 top-1.5 inline-flex h-9 w-9 items-center justify-center rounded-md bg-[#15181B] text-white transition hover:bg-[#B88A3B]"
            />

            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('wishlist.index') }}" @click="mobileOpen = false" class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-lg border border-[#E5E1DA] bg-white px-2 py-3 text-center text-xs font-black text-[#15181B]">
                    <x-store.icon name="heart" class="h-5 w-5 text-[#C9473D]" />
                    <span>Lista</span>
                    <span class="text-[11px] text-[#6B6F74]">{{ $wishlistCount ?? 0 }}</span>
                </a>
                <a href="{{ route('cart.index') }}" @click="mobileOpen = false" class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-lg border border-[#E5E1DA] bg-white px-2 py-3 text-center text-xs font-black text-[#15181B]">
                    <x-store.icon name="cart" class="h-5 w-5 text-[#B88A3B]" />
                    <span>Shporta</span>
                    <span class="text-[11px] text-[#6B6F74]">{{ $cartCount ?? 0 }}</span>
                </a>
                @auth
                    <a href="{{ route('profile.edit') }}" @click="mobileOpen = false" class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-lg border border-[#E5E1DA] bg-white px-2 py-3 text-center text-xs font-black text-[#15181B]">
                        <x-store.icon name="user" class="h-5 w-5 text-[#9A712E]" />
                        <span>Llogaria</span>
                        <span class="truncate text-[11px] text-[#6B6F74]">{{ Str::limit(Auth::user()->name, 12) }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" @click="mobileOpen = false" class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-lg border border-[#E5E1DA] bg-white px-2 py-3 text-center text-xs font-black text-[#15181B]">
                        <x-store.icon name="user" class="h-5 w-5 text-[#9A712E]" />
                        <span>Hyrja</span>
                        <span class="text-[11px] text-[#6B6F74]">Login</span>
                    </a>
                @endauth
            </div>

            @auth
                <div class="rounded-lg border border-[#E5E1DA] bg-white p-2">
                    <a href="{{ route('orders.index') }}" @click="mobileOpen = false" class="flex items-center justify-between rounded-md px-3 py-2 text-sm font-bold text-[#15181B] hover:bg-[#F7F5F1]">
                        Porosite
                        <x-store.icon name="arrow-right" class="h-4 w-4 text-[#B88A3B]" />
                    </a>
                    @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false" class="flex items-center justify-between rounded-md px-3 py-2 text-sm font-bold text-[#9A712E] hover:bg-[#F7F5F1]">
                            Admin
                            <x-store.icon name="arrow-right" class="h-4 w-4 text-[#B88A3B]" />
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm font-bold text-[#15181B] hover:bg-[#F7F5F1]">
                            Dilni
                            <x-store.icon name="arrow-right" class="h-4 w-4 text-[#B88A3B]" />
                        </button>
                    </form>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" @click="mobileOpen = false" class="rounded-lg bg-[#15181B] px-3 py-3 text-center text-sm font-black text-white">Hyni</a>
                    <a href="{{ route('register') }}" @click="mobileOpen = false" class="rounded-lg border border-[#D8D1C6] bg-white px-3 py-3 text-center text-sm font-black text-[#15181B]">Regjistrohu</a>
                </div>
            @endauth

            <nav aria-label="Kategorite mobile" class="space-y-2">
                @foreach($navItems as $item)
                    <div class="rounded-lg border border-[#E5E1DA] bg-white" x-data="{ open: false }">
                        <div class="flex items-center">
                            <a href="{{ $item['href'] }}" @click="mobileOpen = false" class="flex min-w-0 flex-1 items-center gap-3 px-3 py-2.5 font-bold text-[#17191C]">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-[#F7F5F1] text-[#B88A3B]">
                                    <x-store.icon :name="$item['icon']" class="h-4 w-4" />
                                </span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                            @if(($item['children'] ?? collect())->count())
                                <button type="button" class="mr-2 inline-flex h-9 w-9 items-center justify-center rounded-md text-[#6B6F74]" @click="open = !open" :aria-expanded="open.toString()" aria-label="Hap nen-kategorite">
                                    <x-store.icon name="chevron-down" class="h-4 w-4 transition" x-bind:class="{ 'rotate-180': open }" />
                                </button>
                            @endif
                        </div>
                        @if(($item['children'] ?? collect())->count())
                            <div x-show="open" x-cloak x-transition class="border-t border-[#E5E1DA] px-3 py-2">
                                <div class="grid gap-1">
                                    @foreach($item['children']->take(18) as $subcategory)
                                        <a href="{{ $item['category_slug'] ? route('subcategory.show', [$item['category_slug'], $subcategory->slug]) : route('shop', ['subcategory' => $subcategory->slug]) }}"
                                           @click="mobileOpen = false"
                                           class="rounded-md px-3 py-2 text-sm font-semibold text-[#6B6F74] transition hover:bg-[#F7F5F1] hover:text-[#9A712E]">
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
    </aside>
</div>

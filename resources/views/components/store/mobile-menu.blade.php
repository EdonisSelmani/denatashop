@props(['navItems' => collect()])

<div x-show="mobileOpen"
     x-cloak
     class="fixed inset-0 z-50 lg:hidden"
     aria-modal="true"
     role="dialog">
    <div class="absolute inset-0 bg-[#15181B]/55" @click="mobileOpen = false"></div>

    <aside class="absolute right-0 top-0 flex h-full w-full max-w-sm flex-col overflow-y-auto bg-[#F7F5F1] shadow-2xl"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full">
        <div class="flex items-center justify-between border-b border-[#E5E1DA] bg-white px-4 py-3">
            <a href="{{ route('home') }}" class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]" aria-label="Denata Shop">
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="h-[42px] w-auto object-contain">
            </a>
            <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E5E1DA] text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]"
                    @click="mobileOpen = false"
                    aria-label="Mbyll menune">
                <x-store.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <div class="space-y-5 px-4 py-5">
            <form action="{{ route('shop') }}" method="GET">
                <label for="mobile-store-search" class="sr-only">Kerko produkte</label>
                <div class="relative">
                    <input id="mobile-store-search"
                           type="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Kerko produkte, SKU ose kategori"
                           class="w-full rounded-md border border-[#E5E1DA] bg-white py-3 pl-4 pr-12 text-sm text-[#17191C] placeholder:text-[#6B6F74] focus:border-[#B88A3B] focus:ring-[#B88A3B]">
                    <button type="submit" class="absolute right-1.5 top-1.5 inline-flex h-9 w-9 items-center justify-center rounded-md bg-[#15181B] text-white transition hover:bg-[#B88A3B]" aria-label="Kerko">
                        <x-store.icon name="search" class="h-4 w-4" />
                    </button>
                </div>
            </form>

            <nav aria-label="Kategorite mobile" class="space-y-2">
                @foreach($navItems as $item)
                    <div class="rounded-lg border border-[#E5E1DA] bg-white" x-data="{ open: false }">
                        <div class="flex items-center">
                            <a href="{{ $item['href'] }}" class="flex min-w-0 flex-1 items-center gap-3 px-4 py-3 font-bold text-[#17191C]">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-[#F7F5F1] text-[#B88A3B]">
                                    <x-store.icon :name="$item['icon']" class="h-5 w-5" />
                                </span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                            @if(($item['children'] ?? collect())->count())
                                <button type="button" class="mr-2 inline-flex h-9 w-9 items-center justify-center rounded-md text-[#6B6F74]" @click="open = !open" :aria-expanded="open.toString()" aria-label="Hap nen-kategorite">
                                    <x-store.icon name="chevron-down" class="h-4 w-4" />
                                </button>
                            @endif
                        </div>
                        @if(($item['children'] ?? collect())->count())
                            <div x-show="open" x-cloak class="border-t border-[#E5E1DA] px-4 py-3">
                                <div class="grid gap-2">
                                    @foreach($item['children']->take(10) as $subcategory)
                                        <a href="{{ route('shop', ['category' => $item['category_slug'], 'subcategory' => $subcategory->slug]) }}"
                                           class="rounded-md px-3 py-2 text-sm text-[#6B6F74] transition hover:bg-[#F7F5F1] hover:text-[#9A712E]">
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

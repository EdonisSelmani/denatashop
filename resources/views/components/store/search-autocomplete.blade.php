@props([
    'id' => 'store-search',
    'placeholder' => 'Kerko produkt, kategori ose SKU',
    'boxClass' => 'relative mx-auto max-w-2xl',
    'inputClass' => 'h-12 w-full rounded-md border border-[#D8D1C6] bg-[#F7F5F1] pl-4 pr-14 text-sm text-[#17191C] placeholder:text-[#6B6F74] transition focus:border-[#B88A3B] focus:bg-white focus:ring-[#B88A3B]',
    'buttonClass' => 'absolute right-1.5 top-1.5 inline-flex h-9 w-10 items-center justify-center rounded-md bg-[#15181B] text-white transition hover:bg-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]',
])

@php
    $searchEndpoint = route('search.suggestions', [], false);
    $shopEndpoint = route('shop', [], false);
    $resultsId = $id . '-results';
@endphp

<form
    action="{{ $shopEndpoint }}"
    method="GET"
    {{ $attributes }}
    x-data="productSearch(@js($searchEndpoint), @js($shopEndpoint), @js((string) request('search', '')))"
    @submit="submitSearch"
>
    <label for="{{ $id }}" class="sr-only">Kerko produkte</label>
    <div class="{{ $boxClass }}">
        <input
            id="{{ $id }}"
            type="search"
            name="search"
            value="{{ request('search') }}"
            x-model="query"
            @input.debounce.250ms="search"
            @focus="openSuggestions"
            @keydown.down.prevent="move(1)"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="chooseActive"
            @keydown.escape.stop="close"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
            class="{{ $inputClass }}"
            aria-autocomplete="list"
            aria-controls="{{ $resultsId }}"
        >
        <button type="submit" class="{{ $buttonClass }}" aria-label="Kerko">
            <x-store.icon name="search" class="h-4 w-4" />
        </button>

        <div
            id="{{ $resultsId }}"
            x-show="open"
            x-cloak
            x-transition
            @click.outside="close"
            class="absolute left-0 right-0 top-full z-[70] mt-2 overflow-hidden rounded-lg border border-[#E5E1DA] bg-white shadow-2xl"
        >
            <div x-show="loading" class="px-4 py-3 text-sm font-semibold text-[#6B6F74]">
                Duke kerkuar...
            </div>

            <div x-show="!loading && trimmedQuery.length > 0 && suggestions.length === 0" class="px-4 py-4 text-sm font-semibold text-[#6B6F74]">
                Nuk u gjet asnje produkt.
            </div>

            <div x-show="suggestions.length > 0" class="max-h-[420px] overflow-y-auto py-1">
                <template x-for="(item, index) in suggestions" :key="item.sku || item.url">
                    <a
                        :href="item.url"
                        @mouseenter="activeIndex = index"
                        @mousedown.prevent="go(item.url)"
                        class="flex items-center gap-3 px-3 py-2 transition hover:bg-[#F7F5F1]"
                        :class="{ 'bg-[#F7F5F1]': activeIndex === index }"
                    >
                        <img
                            :src="item.thumbnail_url"
                            :alt="item.name"
                            width="48"
                            height="48"
                            class="h-12 w-12 shrink-0 rounded-md border border-[#E5E1DA] bg-[#F7F5F1] object-contain"
                        >
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-black text-[#17191C]" x-text="item.name"></span>
                            <span class="mt-0.5 flex items-center gap-2 text-xs font-semibold text-[#6B6F74]">
                                <span x-text="item.sku"></span>
                            </span>
                        </span>
                        <span class="shrink-0 text-sm font-black text-[#15181B]">&euro;<span x-text="item.price"></span></span>
                    </a>
                </template>
            </div>

            <a
                x-show="hasMore"
                :href="allResultsUrl"
                class="block border-t border-[#E5E1DA] px-4 py-3 text-center text-sm font-black text-[#9A712E] transition hover:bg-[#F7F5F1] hover:text-[#15181B]"
            >
                Shiko te gjitha rezultatet
            </a>
        </div>
    </div>
</form>

@php
    $current = request()->except('page');
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-[#E5E1DA] pb-4">
        <h2 class="text-lg font-black text-[#15181B]">Filtro</h2>
        <a href="{{ route('shop') }}" class="text-sm font-black text-[#9A712E] hover:text-[#15181B]">Pastro</a>
    </div>

    <div>
        <h3 class="text-sm font-black uppercase text-[#6B6F74]">Kategorite</h3>
        <div class="mt-3 space-y-2">
            @foreach($categories as $category)
                @php $isCategoryActive = request('category') === $category->slug; @endphp
                <div class="overflow-hidden rounded-md border {{ $isCategoryActive ? 'border-[#B88A3B] bg-[#B88A3B]/10' : 'border-[#E5E1DA] bg-white' }}">
                    <a href="{{ route('shop', array_merge(request()->except('category', 'subcategory', 'page'), ['category' => $category->slug])) }}"
                       class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm font-black {{ $isCategoryActive ? 'text-[#9A712E]' : 'text-[#22272B]' }}">
                        <span class="truncate">{{ $category->name }}</span>
                        <span class="shrink-0 text-xs text-[#6B6F74]">{{ $category->products_count ?? $category->active_products_count ?? '' }}</span>
                    </a>
                    @if($category->subcategories->count())
                        <div class="border-t border-[#E5E1DA] px-3 py-2">
                            <div class="grid gap-1">
                                @foreach($category->subcategories as $subcategory)
                                    <a href="{{ route('shop', array_merge(request()->except('category', 'subcategory', 'page'), ['category' => $category->slug, 'subcategory' => $subcategory->slug])) }}"
                                       class="rounded px-2 py-1.5 text-sm font-semibold {{ request('subcategory') === $subcategory->slug ? 'bg-white text-[#9A712E] shadow-sm' : 'text-[#6B6F74] hover:bg-[#F7F5F1] hover:text-[#9A712E]' }}">
                                        {{ $subcategory->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <form action="{{ route('shop') }}" method="GET" class="space-y-6">
        @foreach(request()->except('min_price', 'max_price', 'availability', 'page') as $key => $value)
            @if(! is_array($value) && filled($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div>
            <h3 class="text-sm font-black uppercase text-[#6B6F74]">Cmimi</h3>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-[#6B6F74]">Nga</span>
                    <input type="number" min="0" step="0.01" name="min_price" value="{{ request('min_price') }}" class="store-input w-full" placeholder="0">
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-[#6B6F74]">Deri</span>
                    <input type="number" min="0" step="0.01" name="max_price" value="{{ request('max_price') }}" class="store-input w-full" placeholder="200">
                </label>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-black uppercase text-[#6B6F74]">Disponueshmeria</h3>
            <label class="mt-3 flex items-center gap-3 rounded-md border border-[#E5E1DA] bg-white px-3 py-3 text-sm font-bold text-[#22272B] shadow-sm">
                <input type="checkbox" name="availability" value="in_stock" @checked(request('availability') === 'in_stock') class="rounded border-[#D8D1C6] text-[#B88A3B] focus:ring-[#B88A3B]">
                Vetem produkte ne stok
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">Apliko filtrat</button>
    </form>
</div>

@extends('layouts.app')

@section('title', 'Denata Shop - Produkte per shtepi, pune dhe kopsht')
@section('meta_description', 'Denata Shop ofron produkte sanitare, vegla pune, vegla kopshti dhe elektrike me katalog te perditesuar.')

@section('content')
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
    $categoryCards = collect([
        [
            'title' => 'Sanitari',
            'description' => 'Bateri, lidhese dhe produkte per instalime te rregullta ne shtepi.',
            'icon' => 'tap',
            'href' => $sanitaryCategory ? route('category.show', $sanitaryCategory->slug) : route('shop', ['search' => 'sanitari']),
            'count' => $sanitaryCategory?->active_products_count,
            'featured' => true,
        ],
        [
            'title' => 'Vegla Pune',
            'description' => 'Mjete te forta per punetori, montim dhe riparime te perditshme.',
            'icon' => 'wrench',
            'href' => $toolsCategory ? route('category.show', $toolsCategory->slug) : route('shop', ['search' => 'vegla']),
            'count' => $toolsCategory?->active_products_count,
            'featured' => false,
        ],
        [
            'title' => 'Vegla Kopshti',
            'description' => 'Zgjidhje praktike per oborr, kopsht dhe mirembajtje.',
            'icon' => 'leaf',
            'href' => $gardenCategory ? route('category.show', $gardenCategory->slug) : route('shop', ['search' => 'kopsht']),
            'count' => $gardenCategory?->active_products_count,
            'featured' => false,
        ],
        [
            'title' => 'Elektrike',
            'description' => 'Pajisje dhe aksesore elektrike per perdorim te sigurt.',
            'icon' => 'bolt',
            'href' => $electricCategory ? route('category.show', $electricCategory->slug) : route('shop', ['search' => 'elektrike']),
            'count' => $electricCategory?->active_products_count,
            'featured' => false,
        ],
        [
            'title' => 'Bateri',
            'description' => 'Bateri dhe produkte te lidhura per banjo, kuzhine dhe lavaman.',
            'icon' => 'battery',
            'href' => $batterySubcategory ? route('shop', ['category' => $batteryParent?->slug, 'subcategory' => $batterySubcategory->slug]) : route('shop', ['search' => 'bateri']),
            'count' => null,
            'featured' => false,
        ],
    ]);
    $heroProducts = ($featuredProducts->count() ? $featuredProducts : $newProducts)->take(3)->values();
    $sections = collect([
        ['title' => 'Produkte te rekomanduara', 'subtitle' => 'Zgjedhje te forta per pune te perditshme.', 'products' => $featuredProducts, 'badge' => 'Zgjedhur', 'href' => route('shop')],
        ['title' => 'Me te kerkuarat', 'subtitle' => 'Produkte qe vizitohen dhe shtohen shpesh ne shporte.', 'products' => $bestSellers, 'badge' => null, 'href' => route('shop', ['sort' => 'latest'])],
        ['title' => 'Oferta te zgjedhura', 'subtitle' => 'Cmimet me zbritje shfaqen vetem kur ka zbritje reale.', 'products' => $discountProducts, 'badge' => null, 'href' => route('shop')],
        ['title' => 'Produkte te reja', 'subtitle' => 'Artikujt e fundit te shtuar ne katalog.', 'products' => $newProducts, 'badge' => 'E re', 'href' => route('shop', ['sort' => 'latest'])],
    ])->filter(fn ($section) => $section['products']->count());
@endphp

<section class="relative overflow-hidden bg-[#F7F5F1]">
    <div class="absolute inset-x-0 top-0 h-px bg-[#E5E1DA]"></div>
    <div class="container-custom grid gap-10 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center lg:py-14">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 rounded-full border border-[#D8D1C6] bg-white px-4 py-2 text-xs font-black uppercase text-[#9A712E]">
                <x-store.icon name="home" class="h-4 w-4" />
                Per shtepi, pune dhe kopsht
            </p>
            <h1 class="mt-5 text-4xl font-black leading-tight text-[#15181B] sm:text-5xl lg:text-6xl">
                Gjithcka qe te duhet per cdo projekt
            </h1>
            <p class="mt-5 max-w-xl text-base leading-8 text-[#6B6F74]">
                Nga instalimet sanitare te veglat e punes, Denata Shop mban katalog te qarte, cmime te verifikuara dhe produkte te zgjedhura per pune qe duhet te zgjase.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('shop') }}" class="btn-primary inline-flex items-center justify-center gap-2">
                    Shiko produktet
                    <x-store.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="#kategorite" class="btn-secondary inline-flex items-center justify-center gap-2">
                    Eksploro kategorite
                </a>
            </div>
        </div>

        <div class="relative min-h-[360px] overflow-hidden rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_24px_70px_rgba(21,24,27,0.08)]">
            <div class="absolute right-8 top-8 h-24 w-24 rotate-45 border border-[#D8D1C6]"></div>
            <div class="absolute bottom-6 left-8 h-20 w-20 rotate-45 bg-[#B88A3B]/10"></div>
            <div class="relative grid h-full grid-cols-2 gap-4">
                @forelse($heroProducts as $index => $product)
                    <a href="{{ route('product.show', $product->slug) }}" class="{{ $index === 0 ? 'col-span-2 min-h-[190px]' : 'min-h-[132px]' }} group rounded-lg border border-[#E5E1DA] bg-[#F7F5F1] p-4 transition hover:border-[#B88A3B]">
                        <div class="flex h-full items-center gap-4">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" width="260" height="180" class="h-full max-h-44 w-1/2 object-contain">
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase text-[#9A712E]">{{ $product->subcategory?->name }}</p>
                                <h2 class="mt-2 line-clamp-3 text-base font-black text-[#15181B]">{{ $product->name }}</h2>
                                <p class="mt-2 text-lg font-black text-[#15181B]">&euro;{{ number_format((float) $product->price, 2) }}</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-2 flex min-h-[320px] items-center justify-center rounded-lg border border-dashed border-[#D8D1C6]">
                        <p class="text-sm font-semibold text-[#6B6F74]">Produktet do te shfaqen ketu sapo katalogu te kete te dhena.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section id="kategorite" class="container-custom py-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-black uppercase text-[#9A712E]">Kategorite kryesore</p>
            <h2 class="mt-2 text-3xl font-black text-[#15181B]">Katalog i organizuar per pune reale</h2>
        </div>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#9A712E] hover:text-[#15181B]">
            Shiko te gjitha
            <x-store.icon name="arrow-right" class="h-4 w-4" />
        </a>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($categoryCards as $card)
            <x-store.category-card
                :title="$card['title']"
                :description="$card['description']"
                :href="$card['href']"
                :icon="$card['icon']"
                :count="$card['count']"
                :featured="$card['featured']" />
        @endforeach
    </div>
</section>

<section class="container-custom py-6">
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-store.trust-item icon="shield" title="Produkte cilesore" text="Artikuj te zgjedhur per perdorim ne shtepi, punetori dhe instalime." />
        <x-store.trust-item icon="truck" title="Dergese ne Kosove" text="Porosite pergatiten me kujdes dhe dergohen ne qytetet e Kosoves." />
        <x-store.trust-item icon="lock" title="Pagese e sigurt" text="Proces i qarte porosie dhe ruajtje e kujdesshme e te dhenave." />
        <x-store.trust-item icon="headset" title="Mbeshtetje per kliente" text="Ndihme per zgjedhjen e produktit dhe informata rreth disponueshmerise." />
    </div>
</section>

@foreach($sections as $section)
    <section class="container-custom py-9">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-[#15181B]">{{ $section['title'] }}</h2>
                <p class="mt-1 text-sm text-[#6B6F74]">{{ $section['subtitle'] }}</p>
            </div>
            <a href="{{ $section['href'] }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#9A712E] hover:text-[#15181B]">
                Shiko me shume
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($section['products'] as $product)
                <x-store.product-card :product="$product" :badge="$section['badge']" />
            @endforeach
        </div>
    </section>
@endforeach

<section class="container-custom py-12">
    <div class="overflow-hidden rounded-lg border border-[#E5E1DA] bg-[#15181B] p-6 text-white sm:p-8 lg:p-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="text-sm font-black uppercase text-[#D7B16D]">Denata Shop</p>
                <h2 class="mt-2 text-3xl font-black">Partneri yt per shtepi dhe pune</h2>
                <p class="mt-4 max-w-3xl leading-8 text-[#E5E1DA]">
                    Denata Shop sjell produkte te zgjedhura per instalime sanitare, vegla pune, kopsht dhe elektrike, me fokus ne cilesi dhe sherbim te besueshem.
                </p>
            </div>
            <a href="{{ route('shop') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-white px-5 py-3 text-sm font-black text-[#15181B] transition hover:bg-[#D7B16D]">
                Hyr ne dyqan
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </div>
</section>
@endsection

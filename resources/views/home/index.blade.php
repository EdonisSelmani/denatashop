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

<section class="relative overflow-hidden bg-[#15181B] text-white">
    <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 44px 44px;"></div>
    <div class="container-custom relative grid gap-8 py-8 md:py-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:py-14">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.08] px-4 py-2 text-xs font-black uppercase text-[#D7B16D]">
                <x-store.icon name="home" class="h-4 w-4" />
                Per shtepi, pune dhe kopsht
            </p>
            <h1 class="mt-5 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                Gjithcka qe te duhet per cdo projekt
            </h1>
            <p class="mt-5 max-w-xl text-base leading-8 text-[#E5E1DA]">
                Nga instalimet sanitare te veglat e punes, Denata Shop mban katalog te qarte, cmime te verifikuara dhe produkte te zgjedhura per pune qe duhet te zgjase.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('shop') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#D7B16D] px-6 py-3 font-black text-[#15181B] shadow-[0_18px_40px_rgba(215,177,109,0.24)] transition hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D7B16D]">
                    Shiko produktet
                    <x-store.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="#kategorite" class="inline-flex items-center justify-center gap-2 rounded-md border border-white/20 bg-white/10 px-6 py-3 font-black text-white transition hover:border-[#D7B16D] hover:text-[#D7B16D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D7B16D]">
                    Eksploro kategorite
                </a>
            </div>
            <div class="mt-8 grid max-w-xl grid-cols-3 divide-x divide-white/10 border-y border-white/10 py-4">
                <div class="pr-3">
                    <p class="text-2xl font-black text-white">{{ $categoryCards->count() }}</p>
                    <p class="mt-1 text-xs font-bold uppercase text-[#B8B0A4]">Kategorite</p>
                </div>
                <div class="px-3">
                    <p class="text-2xl font-black text-white">{{ ($featuredProducts->count() + $newProducts->count()) }}</p>
                    <p class="mt-1 text-xs font-bold uppercase text-[#B8B0A4]">Zgjedhje</p>
                </div>
                <div class="pl-3">
                    <p class="text-2xl font-black text-white">{{ $discountProducts->count() }}</p>
                    <p class="mt-1 text-xs font-bold uppercase text-[#B8B0A4]">Oferta</p>
                </div>
            </div>
        </div>

        <div class="relative min-h-[330px] md:min-h-[410px]">
            <div class="absolute -right-4 top-4 h-[88%] w-[82%] border border-white/10 bg-white/5"></div>
            <div class="relative grid h-full grid-cols-2 gap-3 sm:gap-4">
                @forelse($heroProducts as $index => $product)
                    <a href="{{ route('product.show', $product->slug) }}" class="{{ $index === 0 ? 'col-span-2 min-h-[188px] sm:min-h-[220px]' : 'min-h-[136px]' }} group rounded-lg border border-white/10 bg-white p-4 text-[#15181B] shadow-[0_24px_70px_rgba(0,0,0,0.26)] transition hover:-translate-y-0.5 hover:border-[#D7B16D]">
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
                    <div class="col-span-2 flex min-h-[320px] items-center justify-center rounded-lg border border-dashed border-white/25 bg-white/[0.08]">
                        <p class="text-sm font-semibold text-[#E5E1DA]">Produktet do te shfaqen ketu sapo katalogu te kete te dhena.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section id="kategorite" class="container-custom py-10 md:py-12">
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($section['products'] as $product)
                <x-store.product-card :product="$product" :badge="$section['badge']" />
            @endforeach
        </div>
    </section>
@endforeach

<section class="container-custom py-12">
    <div class="overflow-hidden rounded-lg border border-[#2A2D31] bg-[#15181B] p-6 text-white shadow-[0_28px_80px_rgba(21,24,27,0.16)] sm:p-8 lg:p-10">
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

@extends('layouts.app')

@section('title', 'DenataShop - Sanitari, vegla pune dhe produkte për shtëpi në Kosovë')
@section('meta_description', 'Blej sanitari, vegla pune, vegla kopshti, ujësjellës dhe elektronike në DenataShop. Katalog i qartë për projekte shtëpie në Kosovë.')
@section('canonical', App\Support\Seo::canonical(route('home', [], false)))
@section('seo_image', App\Support\Seo::image('images/denata-home-hero-products.png'))

@php
    $structuredData = [
        App\Support\Seo::websiteSchema(),
        App\Support\Seo::organizationSchema(),
        App\Support\Seo::breadcrumbSchema([
            ['name' => 'Ballina', 'url' => route('home', [], false)],
        ]),
    ];
@endphp

@section('content')
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

    $assetThumb = fn ($path) => asset('storage/product-thumbs/' . $path);

    $categoryCards = collect([
        [
            'title' => 'Sanitari',
            'icon' => 'tap',
            'image' => $assetThumb('products/bateria/BAT001.webp'),
            'href' => $sanitaryCategory ? route('category.show', $sanitaryCategory->slug) : route('shop', ['search' => 'sanitari']),
        ],
        [
            'title' => 'Vegla Pune',
            'icon' => 'wrench',
            'image' => $assetThumb('products/vs/MJETE-PUNE-VS-ZHWE-VS/21346.webp'),
            'href' => $toolsCategory ? route('category.show', $toolsCategory->slug) : route('shop', ['search' => 'vegla pune']),
        ],
        [
            'title' => 'Vegla Kopshti',
            'icon' => 'leaf',
            'image' => $assetThumb('products/vs/MJETE-PUNE-VS/22238.webp'),
            'href' => $gardenCategory ? route('category.show', $gardenCategory->slug) : route('shop', ['search' => 'vegla kopshti']),
        ],
        [
            'title' => 'Elektronike',
            'icon' => 'bolt',
            'image' => $assetThumb('products/vs/MJETE-PUNE-VS/21457.webp'),
            'href' => $electricCategory ? route('category.show', $electricCategory->slug) : route('shop', ['search' => 'elektronike']),
        ],
        [
            'title' => 'Ujësjellës',
            'icon' => 'tap',
            'image' => $assetThumb('products/bateria/BAT090.webp'),
            'href' => $plumbingCategory ? route('category.show', $plumbingCategory->slug) : route('shop', ['search' => 'ujesjelles']),
        ],
    ]);

    $heroImage = asset('images/denata-home-hero-products.png');

    $sections = collect([
        ['title' => 'Produkte të rekomanduara', 'subtitle' => 'Zgjedhje të forta për punë të përditshme.', 'products' => $featuredProducts, 'badge' => 'Zgjedhur', 'href' => route('shop')],
        ['title' => 'Më të kërkuarat', 'subtitle' => 'Produkte që vizitohen dhe shtohen shpesh në shportë.', 'products' => $bestSellers, 'badge' => null, 'href' => route('shop', ['sort' => 'latest'])],
        ['title' => 'Oferta të zgjedhura', 'subtitle' => 'Çmimet me zbritje shfaqen vetëm kur ka zbritje reale.', 'products' => $discountProducts, 'badge' => null, 'href' => route('shop')],
        ['title' => 'Produkte të reja', 'subtitle' => 'Artikujt e fundit të shtuar në katalog.', 'products' => $newProducts, 'badge' => 'E re', 'href' => route('shop', ['sort' => 'latest'])],
    ])->filter(fn ($section) => $section['products']->count());
@endphp

<section class="relative overflow-hidden border-b border-[#E5E7EB] bg-[#F7F6F3]">
    <div class="absolute inset-y-0 right-0 hidden w-[55%] lg:block" aria-hidden="true">
        <img src="{{ $heroImage }}" alt="" width="846" height="484" loading="eager" fetchpriority="high" class="h-full w-full object-cover object-center">
        <div class="absolute inset-y-0 left-0 w-[22%] bg-gradient-to-r from-[#F7F6F3] via-[#F7F6F3]/80 to-transparent"></div>
    </div>
    <div class="pointer-events-none absolute right-[-18%] top-8 h-[360px] w-[360px] rounded-full bg-white/65 md:right-[-8%] lg:hidden" aria-hidden="true"></div>

    <div class="container-custom relative grid min-h-[455px] gap-6 py-7 md:py-8 lg:grid-cols-[45%_55%] lg:items-center lg:py-0">
        <div class="relative z-10 flex max-w-[560px] flex-col justify-center">
            <h1 class="text-4xl font-black leading-[1.06] text-[#111111] sm:text-5xl lg:text-[52px] xl:text-[56px]">
                <span class="block xl:whitespace-nowrap">Gjithçka që të duhet</span>
                <span class="block">për çdo projekt</span>
            </h1>
            <p class="mt-5 max-w-xl text-base leading-8 text-[#6B7280] sm:text-lg">
                Nga instalimet sanitare te veglat e punës, Denata Shop mban katalog të qartë, çmime të verifikuara dhe produkte të zgjedhura për punë që duhet të zgjasë.
            </p>
            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('shop') }}" class="inline-flex items-center justify-center gap-3 rounded-md bg-[#111111] px-7 py-4 text-base font-black text-white shadow-[0_16px_32px_rgba(17,17,17,0.16)] transition hover:bg-[#C9A14A] hover:text-[#111111] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]">
                    Shiko produktet
                    <x-store.icon name="arrow-right" class="h-5 w-5" />
                </a>
                <a href="#kategorite" class="inline-flex items-center justify-center rounded-md border border-[#D7D9DE] bg-white px-7 py-4 text-base font-black text-[#111111] shadow-[0_10px_24px_rgba(17,17,17,0.04)] transition hover:border-[#C9A14A] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]">
                    Eksploro kategoritë
                </a>
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[760px] lg:hidden" aria-label="Koleksion produktesh Denata Shop">
            <img src="{{ $heroImage }}" alt="Bateri, vegla pune, karrocë, ndriçim dhe aksesorë ujësjellësi Denata Shop" width="846" height="484" loading="eager" fetchpriority="high" class="-mx-4 mt-2 h-auto w-[calc(100%+2rem)] max-w-none object-cover">
        </div>
    </div>
</section>

<section id="kategorite" class="bg-white py-5 md:py-6">
    <div class="container-custom">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach($categoryCards as $card)
                <x-store.category-card
                    :title="$card['title']"
                    :href="$card['href']"
                    :icon="$card['icon']"
                    :image="$card['image']" />
            @endforeach
        </div>
    </div>
</section>

<section class="container-custom py-8 md:py-10">
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-store.trust-item icon="shield" title="Produkte cilësore" text="Artikuj të zgjedhur për përdorim në shtëpi, punëtori dhe instalime." />
        <x-store.trust-item icon="truck" title="Dërgesë në Kosovë" text="Porositë përgatiten me kujdes dhe dërgohen në qytetet e Kosovës." />
        <x-store.trust-item icon="lock" title="Pagesë e sigurt" text="Proces i qartë porosie dhe ruajtje e kujdesshme e të dhënave." />
        <x-store.trust-item icon="headset" title="Mbështetje për klientë" text="Ndihmë për zgjedhjen e produktit dhe informata rreth disponueshmërisë." />
    </div>
</section>

@foreach($sections as $section)
    <section class="container-custom py-9">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-[#111111]">{{ $section['title'] }}</h2>
                <p class="mt-1 text-sm text-[#6B7280]">{{ $section['subtitle'] }}</p>
            </div>
            <a href="{{ $section['href'] }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#9A712E] hover:text-[#111111]">
                Shiko më shumë
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
    <div class="overflow-hidden rounded-lg border border-[#E5E7EB] bg-white p-6 shadow-[0_18px_50px_rgba(17,17,17,0.06)] sm:p-8 lg:p-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="text-sm font-black uppercase text-[#9A712E]">Denata Shop</p>
                <h2 class="mt-2 text-3xl font-black text-[#111111]">Partneri yt për shtëpi dhe punë</h2>
                <p class="mt-4 max-w-3xl leading-8 text-[#6B7280]">
                    DenataShop sjell produkte të zgjedhura për banjo, instalime ujësjellësi, vegla pune, kopsht dhe projekte elektrike në Kosovë, me katalog të organizuar për zgjedhje më të lehtë.
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($categoryCards as $card)
                        <a href="{{ $card['href'] }}" class="rounded-full border border-[#E5E7EB] bg-[#F7F6F3] px-3 py-1.5 text-sm font-bold text-[#111111] transition hover:border-[#C9A14A] hover:text-[#9A712E]">
                            {{ $card['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('shop') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#111111] px-5 py-3 text-sm font-black text-white transition hover:bg-[#C9A14A] hover:text-[#111111]">
                Hyr në dyqan
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </div>
</section>
@endsection

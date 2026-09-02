@extends('layouts.app')

@section('title', 'Faqja nuk u gjet | DenataShop')
@section('meta_description', 'Faqja e kërkuar nuk u gjet. Kthehu te DenataShop për të shfletuar produktet dhe kategoritë kryesore.')
@section('canonical', App\Support\Seo::canonical(route('home', [], false)))
@section('robots', 'noindex,follow')

@section('content')
<section class="bg-[#F7F6F3]">
    <div class="container-custom py-14 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-3xl rounded-lg border border-[#E5E7EB] bg-white p-7 text-center shadow-[0_18px_50px_rgba(17,17,17,0.06)] sm:p-10">
            <p class="text-sm font-black uppercase text-[#9A712E]">404</p>
            <h1 class="mt-3 text-3xl font-black text-[#111111] sm:text-4xl">Faqja nuk u gjet</h1>
            <p class="mx-auto mt-4 max-w-2xl leading-8 text-[#6B7280]">
                Linku mund të jetë ndryshuar ose produkti nuk është më i disponueshëm. Mund të kthehesh në katalog dhe të shfletosh kategoritë kryesore.
            </p>

            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn-primary inline-flex items-center justify-center gap-2">
                    Ballina
                    <x-store.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('shop') }}" class="btn-secondary inline-flex items-center justify-center gap-2">
                    Shiko katalogun
                </a>
            </div>

            @if(($categories ?? collect())->count())
                <div class="mt-8 border-t border-[#E5E7EB] pt-6">
                    <h2 class="text-sm font-black uppercase text-[#6B7280]">Kategoritë kryesore</h2>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        @foreach($categories->take(6) as $category)
                            <a href="{{ route('category.show', $category->slug) }}" class="rounded-full border border-[#E5E7EB] bg-[#F7F6F3] px-3 py-1.5 text-sm font-bold text-[#111111] transition hover:border-[#C9A14A] hover:text-[#9A712E]">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

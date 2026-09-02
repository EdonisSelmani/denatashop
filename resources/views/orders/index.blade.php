@extends('layouts.app')

@section('title', 'Porosite e mia | Denata Shop')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="bg-[#F7F5F1]">
    <div class="container-custom py-8 sm:py-10">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm font-semibold text-[#6B6F74]">
            <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
            <span>/</span>
            <span class="text-[#15181B]">Porosite</span>
        </nav>

        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-sm font-black uppercase text-[#9A712E]">Llogaria</p>
                <h1 class="mt-2 text-3xl font-black text-[#15181B] sm:text-4xl">Porosite e mia</h1>
            </div>
            <a href="{{ route('shop') }}" class="btn-secondary inline-flex w-fit items-center justify-center gap-2">
                Vazhdoni blerjen
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>

        <div class="overflow-hidden rounded-lg border border-[#E5E1DA] bg-white shadow-[0_18px_45px_rgba(21,24,27,0.06)]">
            @forelse($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex flex-col gap-4 border-b border-[#E5E1DA] p-5 transition last:border-b-0 hover:bg-[#F7F5F1] sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-black text-[#15181B]">{{ $order->order_number }}</p>
                        <p class="mt-1 text-sm font-semibold text-[#6B6F74]">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full border border-[#D8D1C6] bg-[#F7F5F1] px-3 py-1 text-xs font-black uppercase text-[#22272B]">{{ ucfirst($order->status) }}</span>
                        <span class="text-lg font-black text-[#15181B]">&euro;{{ number_format($order->total, 2) }}</span>
                    </div>
                </a>
            @empty
                <x-store.empty-state
                    icon="package"
                    title="Nuk keni ende porosi"
                    message="Kur te perfundoni nje blerje, porosite do te shfaqen ketu."
                    :href="route('shop')"
                    action="Filloni blerjet" />
            @endforelse
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

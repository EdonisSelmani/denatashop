@extends('layouts.app')

@section('title', 'Porosia ' . $order->order_number . ' | Denata Shop')

@section('content')
<div class="bg-[#F7F5F1]">
    <div class="container-custom py-8 sm:py-10">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm font-semibold text-[#6B6F74]">
            <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
            <span>/</span>
            <a href="{{ route('orders.index') }}" class="hover:text-[#9A712E]">Porosite</a>
            <span>/</span>
            <span class="text-[#15181B]">{{ $order->order_number }}</span>
        </nav>

        @if(session('success'))
            <div class="mb-6 rounded-md border border-[#25865A]/30 bg-[#25865A]/10 px-4 py-3 text-sm font-semibold text-[#1f6d49]">
                {{ session('success') }}
            </div>
        @endif

        <section class="mb-6 rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)] sm:p-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-black uppercase text-[#9A712E]">Porosia</p>
                    <h1 class="mt-2 text-3xl font-black text-[#15181B]">{{ $order->order_number }}</h1>
                    <p class="mt-1 text-sm font-semibold text-[#6B6F74]">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <span class="w-fit rounded-full border border-[#D8D1C6] bg-[#F7F5F1] px-3 py-1 text-xs font-black uppercase text-[#22272B]">{{ ucfirst($order->status) }}</span>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <section class="overflow-hidden rounded-lg border border-[#E5E1DA] bg-white shadow-[0_18px_45px_rgba(21,24,27,0.06)]">
                <div class="border-b border-[#E5E1DA] px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-black text-[#15181B]">Produktet</h2>
                </div>
                @foreach($order->items as $item)
                    <div class="grid gap-3 border-b border-[#E5E1DA] p-5 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:px-6">
                        <div class="min-w-0">
                            <p class="font-black text-[#15181B]">{{ $item->product_name }}</p>
                            <p class="mt-1 text-sm font-semibold text-[#6B6F74]">SKU: {{ $item->product_sku ?? '-' }}</p>
                        </div>
                        <p class="text-sm font-semibold text-[#6B6F74]">{{ $item->quantity }} x &euro;{{ number_format($item->unit_price, 2) }}</p>
                        <p class="font-black text-[#15181B]">&euro;{{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
            </section>

            <aside class="h-fit rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)]">
                <h2 class="text-lg font-black text-[#15181B]">Detajet</h2>
                <div class="mt-4 space-y-3 border-b border-[#E5E1DA] pb-5 text-sm font-semibold text-[#6B6F74]">
                    <p><span class="font-black text-[#22272B]">Emri:</span> {{ $order->customer_name }}</p>
                    <p><span class="font-black text-[#22272B]">Telefoni:</span> {{ $order->customer_phone }}</p>
                    <p><span class="font-black text-[#22272B]">Adresa:</span> {{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                    <p><span class="font-black text-[#22272B]">Pagesa:</span> Cash on delivery</p>
                    @if($order->tracking_number)
                        <p><span class="font-black text-[#22272B]">Tracking:</span> {{ $order->tracking_number }}</p>
                    @endif
                </div>

                <div class="space-y-3 border-b border-[#E5E1DA] py-5 text-sm font-semibold text-[#6B6F74]">
                    <div class="flex justify-between gap-4"><span>Subtotal</span><span class="font-black text-[#15181B]">&euro;{{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="flex justify-between gap-4"><span>Transporti</span><span class="font-black text-[#15181B]">&euro;{{ number_format($order->shipping_total, 2) }}</span></div>
                    @if($order->member_discount_total > 0)
                        <div class="flex justify-between gap-4 text-[#25865A]"><span>Zbritje llogarie 7%</span><span>-&euro;{{ number_format($order->member_discount_total, 2) }}</span></div>
                    @endif
                    @if($order->coupon_code && ($order->discount_total - $order->member_discount_total) > 0)
                        <div class="flex justify-between gap-4 text-[#25865A]"><span>Zbritje {{ $order->coupon_code }}</span><span>-&euro;{{ number_format($order->discount_total - $order->member_discount_total, 2) }}</span></div>
                    @endif
                </div>

                <div class="flex justify-between gap-4 pt-5 text-xl font-black text-[#15181B]">
                    <span>Total</span>
                    <span>&euro;{{ number_format($order->total, 2) }}</span>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Porosia ' . $order->order_number)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Porosia {{ $order->order_number }}</h1>
                <p class="text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm w-fit">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="font-semibold">Produktet</h2>
            </div>
            @foreach($order->items as $item)
                <div class="flex items-center gap-4 p-6 border-b last:border-b-0">
                    <div class="flex-1">
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-sm text-gray-500">SKU: {{ $item->product_sku ?? '-' }}</p>
                    </div>
                    <p class="text-sm text-gray-600">{{ $item->quantity }} x €{{ number_format($item->unit_price, 2) }}</p>
                    <p class="font-semibold">€{{ number_format($item->total, 2) }}</p>
                </div>
            @endforeach
        </div>

        <aside class="bg-white rounded-lg shadow-md p-6 h-fit">
            <h2 class="font-semibold mb-4">Detajet</h2>
            <div class="space-y-2 text-sm border-b pb-4">
                <p><span class="text-gray-500">Emri:</span> {{ $order->customer_name }}</p>
                <p><span class="text-gray-500">Telefoni:</span> {{ $order->customer_phone }}</p>
                <p><span class="text-gray-500">Adresa:</span> {{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                <p><span class="text-gray-500">Pagesa:</span> Cash on delivery</p>
                @if($order->tracking_number)
                    <p><span class="text-gray-500">Tracking:</span> {{ $order->tracking_number }}</p>
                @endif
            </div>
            <div class="space-y-2 py-4 border-b">
                <div class="flex justify-between"><span>Subtotal</span><span>€{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span>Transporti</span><span>€{{ number_format($order->shipping_total, 2) }}</span></div>
                @if($order->member_discount_total > 0)
                    <div class="flex justify-between text-green-700"><span>Zbritje llogarie 7%</span><span>-€{{ number_format($order->member_discount_total, 2) }}</span></div>
                @endif
                @if($order->coupon_code && ($order->discount_total - $order->member_discount_total) > 0)
                    <div class="flex justify-between text-green-700"><span>Zbritje {{ $order->coupon_code }}</span><span>-€{{ number_format($order->discount_total - $order->member_discount_total, 2) }}</span></div>
                @endif
            </div>
            <div class="flex justify-between pt-4 text-lg font-bold">
                <span>Total</span>
                <span>€{{ number_format($order->total, 2) }}</span>
            </div>
        </aside>
    </div>
</div>
@endsection

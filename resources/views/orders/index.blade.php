@extends('layouts.app')

@section('title', 'Porosite e mia')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold mb-8">Porosite e mia</h1>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @forelse($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-5 border-b hover:bg-gray-50">
                <div>
                    <p class="font-semibold">{{ $order->order_number }}</p>
                    <p class="text-sm text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">{{ ucfirst($order->status) }}</span>
                    <span class="font-bold">€{{ number_format($order->total, 2) }}</span>
                </div>
            </a>
        @empty
            <div class="p-8 text-center">
                <p class="text-gray-600 mb-4">Nuk keni ende porosi.</p>
                <a href="{{ route('shop') }}" class="inline-block bg-blue-600 text-white px-5 py-2 rounded-lg">Filloni blerjet</a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
@endsection

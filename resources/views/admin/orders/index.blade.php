@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold">Orders</h1>

            <form method="GET" action="{{ route('admin.orders.index') }}">
                <select name="status" onchange="this.form.submit()" class="border rounded-lg px-3 py-2">
                    <option value="">All statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium">{{ $order->order_number }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div>{{ $order->customer_name }}</div>
                                    <div class="text-gray-500">{{ $order->customer_phone }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $statuses[$order->status] ?? ucfirst($order->status) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div>{{ ucfirst($order->payment_status) }}</div>
                                    @if($order->tracking_number)
                                        <div class="text-gray-500">{{ $order->tracking_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold">€{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 text-sm">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

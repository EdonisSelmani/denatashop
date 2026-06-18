@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Order {{ $order->order_number }}</h1>
                <p class="text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-800">Back to orders</a>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="font-semibold">Items</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium">{{ $item->product_name }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $item->product_sku ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm">€{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold">€{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="font-semibold mb-4">Update order</h2>
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border rounded-lg px-3 py-2">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment status</label>
                            <select name="payment_status" class="w-full border rounded-lg px-3 py-2">
                                @foreach($paymentStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tracking number</label>
                            <input name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Save</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="font-semibold mb-4">Customer</h2>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-gray-500">Name:</span> {{ $order->customer_name }}</p>
                        <p><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</p>
                        <p><span class="text-gray-500">Phone:</span> {{ $order->customer_phone }}</p>
                        <p><span class="text-gray-500">City:</span> {{ $order->shipping_city }}</p>
                        <p><span class="text-gray-500">Address:</span> {{ $order->shipping_address }}</p>
                        @if($order->tracking_number)
                            <p><span class="text-gray-500">Tracking:</span> {{ $order->tracking_number }}</p>
                        @endif
                        @if($order->notes)
                            <p><span class="text-gray-500">Notes:</span> {{ $order->notes }}</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="font-semibold mb-4">Totals</h2>
                    <div class="space-y-2 text-sm border-b pb-4">
                        <div class="flex justify-between"><span>Subtotal</span><span>€{{ number_format($order->subtotal, 2) }}</span></div>
                        <div class="flex justify-between"><span>Shipping</span><span>€{{ number_format($order->shipping_total, 2) }}</span></div>
                        @if($order->member_discount_total > 0)
                            <div class="flex justify-between text-green-700"><span>Member discount 7%</span><span>-€{{ number_format($order->member_discount_total, 2) }}</span></div>
                        @endif
                        @if($order->coupon_code && ($order->discount_total - $order->member_discount_total) > 0)
                            <div class="flex justify-between text-green-700"><span>Discount {{ $order->coupon_code }}</span><span>-€{{ number_format($order->discount_total - $order->member_discount_total, 2) }}</span></div>
                        @endif
                    </div>
                    <div class="flex justify-between pt-4 text-lg font-bold">
                        <span>Total</span>
                        <span>€{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

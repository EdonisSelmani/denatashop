@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold mb-8">Checkout</h1>

    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if($couponError)
        <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-6">
            {{ $couponError }}
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-3 gap-8">
        @csrf

        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-6">Te dhenat e dergeses</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emri dhe mbiemri</label>
                    <input name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" class="w-full border rounded-lg px-3 py-2" required>
                    @error('customer_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" class="w-full border rounded-lg px-3 py-2" required>
                    @error('customer_email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefoni</label>
                    <input name="customer_phone" value="{{ old('customer_phone') }}" class="w-full border rounded-lg px-3 py-2" required>
                    @error('customer_phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Qyteti</label>
                    <input name="shipping_city" value="{{ old('shipping_city') }}" class="w-full border rounded-lg px-3 py-2" required>
                    @error('shipping_city') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresa</label>
                    <input name="shipping_address" value="{{ old('shipping_address') }}" class="w-full border rounded-lg px-3 py-2" required>
                    @error('shipping_address') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kodi postar</label>
                    <input name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" class="w-full border rounded-lg px-3 py-2">
                    @error('shipping_postal_code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kuponi</label>
                    <input name="coupon_code" value="{{ old('coupon_code', request('coupon_code')) }}" class="w-full border rounded-lg px-3 py-2" placeholder="p.sh. DENATA10">
                    @error('coupon_code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shenime</label>
                    <textarea name="notes" rows="4" class="w-full border rounded-lg px-3 py-2">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <aside class="bg-white rounded-lg shadow-md p-6 h-fit sticky top-24">
            <h2 class="text-xl font-semibold mb-4">Porosia juaj</h2>

            <div class="space-y-4 border-b pb-4">
                @foreach($cartItems as $item)
                    <div class="flex gap-3">
                        <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" loading="lazy" decoding="async" class="w-16 h-16 rounded object-cover">
                        <div class="flex-1">
                            <p class="font-medium text-sm">{{ $item->product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->quantity }} x €{{ number_format($item->product->price, 2) }}</p>
                        </div>
                        <p class="font-semibold text-sm">€{{ number_format($item->product->price * $item->quantity, 2) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="space-y-2 py-4 border-b">
                <div class="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span>€{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Transporti</span>
                    <span>€{{ number_format($shippingTotal, 2) }}</span>
                </div>
                @if(($memberDiscountTotal ?? 0) > 0)
                    <div class="flex justify-between text-sm text-green-700">
                        <span>Zbritje llogarie 7%</span>
                        <span>-€{{ number_format($memberDiscountTotal, 2) }}</span>
                    </div>
                @else
                    <div class="text-sm text-blue-700 bg-blue-50 rounded p-2">
                        Hape nje llogari dhe perfito 7% zbritje ne produktet e porosise.
                    </div>
                @endif
                @if(($couponDiscountTotal ?? 0) > 0)
                    <div class="flex justify-between text-sm text-green-700">
                        <span>Zbritje {{ $coupon?->code }}</span>
                        <span>-€{{ number_format($couponDiscountTotal, 2) }}</span>
                    </div>
                @endif
            </div>

            <div class="flex justify-between py-4 text-lg font-bold">
                <span>Total</span>
                <span>€{{ number_format($total, 2) }}</span>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600 mb-4">
                Pagesa behet me para ne dore gjate dorezimit.
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                Konfirmo porosine
            </button>
        </aside>
    </form>
</div>
@endsection

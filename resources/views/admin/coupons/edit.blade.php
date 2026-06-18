@extends('layouts.admin')

@section('title', 'Edit Coupon')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">Edit coupon</h1>
            <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
                @method('PUT')
                @include('admin.coupons._form')
            </form>
        </div>
    </div>
</div>
@endsection

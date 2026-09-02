{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>Admin - @yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#15181B">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Admin Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-gray-800">
                            Admin Panel
                        </a>
                        
                        <div class="hidden md:flex items-center ml-10 space-x-4">
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Dashboard</a>
                            <a href="{{ route('admin.categories.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Categories</a>
                            <a href="{{ route('admin.subcategories.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Subcategories</a>
                            <a href="{{ route('admin.products.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Products</a>
                            <a href="{{ route('admin.coupons.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Coupons</a>
                            <a href="{{ route('admin.orders.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Orders</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-gray-900">View Store</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-gray-900">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <main>
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>
</body>
</html>

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total');
        
        $recentProducts = Product::latest()->limit(5)->get();
        $recentOrders = Order::latest()->limit(5)->get();
        
        $salesData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [0, 0, 0, 0, 0, 0]
        ];
        
        return view('admin.dashboard', compact(
            'totalProducts', 'activeProducts', 'totalUsers', 
            'totalOrders', 'totalRevenue', 'recentProducts', 'recentOrders', 'salesData'
        ));
    }
}

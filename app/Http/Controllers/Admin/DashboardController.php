<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'products_count' => Product::count(),
            'categories_count' => Category::count(),
            'orders_count' => Order::count(),
            'customers_count' => Customer::count(),
            'revenue_total' => Order::where('payment_status', 'paid')->sum('total'),
            'orders_pending' => Order::where('status', 'pending')->count(),
            'orders_processing' => Order::where('status', 'processing')->count(),
            'low_stock' => Product::where('is_active', true)->where('sort_order', '<', 5)->count(),
        ];

        $recentOrders = Order::with('customer')->latest()->take(8)->get();
        $recentProducts = Product::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentProducts'));
    }
}

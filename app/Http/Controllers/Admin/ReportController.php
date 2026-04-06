<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', '30');

        $from = now()->subDays((int) $period)->startOfDay();

        $revenueTotal = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $from)
            ->sum('total');

        $ordersTotal = Order::where('created_at', '>=', $from)->count();

        $newCustomers = Customer::where('created_at', '>=', $from)->count();

        $avgOrderValue = $ordersTotal > 0 ? $revenueTotal / $ordersTotal : 0;

        $ordersByStatus = Order::where('created_at', '>=', $from)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $from)
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $dailyRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $from)
            ->selectRaw("DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.index', compact(
            'period', 'revenueTotal', 'ordersTotal', 'newCustomers',
            'avgOrderValue', 'ordersByStatus', 'topProducts', 'dailyRevenue'
        ));
    }
}

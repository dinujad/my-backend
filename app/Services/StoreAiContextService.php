<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StoreAiContextService
{
    /**
     * Compact JSON context injected into Gemini prompts (store data only).
     */
    public function buildCompactContext(): array
    {
        $paidRevenue = (float) Order::where('payment_status', 'paid')->sum('total');

        return [
            'store_name' => config('ai.store_name', config('app.name', 'Print Works.LK')),
            'generated_at' => now()->toIso8601String(),
            'currency' => 'LKR',
            'summary' => [
                'total_products' => Product::count(),
                'active_products' => Product::active()->count(),
                'categories' => Category::count(),
                'customers' => Customer::count(),
                'total_orders' => Order::count(),
                'paid_orders' => Order::where('payment_status', 'paid')->count(),
                'total_revenue_lkr' => round($paidRevenue, 2),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'processing_orders' => Order::where('status', 'processing')->count(),
            ],
            'periods' => [
                'today' => $this->periodStats('today'),
                'yesterday' => $this->periodStats('yesterday'),
                'this_week' => $this->periodStats('this_week'),
                'last_7_days' => $this->periodStats('last_7_days'),
                'last_30_days' => $this->periodStats('last_30_days'),
                'this_month' => $this->periodStats('this_month'),
                'last_month' => $this->periodStats('last_month'),
            ],
            'top_products_last_30_days' => $this->topProducts('last_30_days', 10),
            'low_stock_products' => $this->lowStockProducts(15),
            'products_by_category' => $this->productsByCategory(12),
            'recent_orders' => $this->recentOrders(8),
            'active_product_names' => Product::active()
                ->orderByDesc('updated_at')
                ->limit(60)
                ->pluck('name')
                ->values()
                ->all(),
        ];
    }

    /**
     * Overview / predictions payload for admin Blade views.
     */
    public function getOverview(string $period = 'last_30_days'): array
    {
        $current = $this->periodStats($period);
        $previous = $this->previousPeriodStats($period);

        $revenueChange = $this->percentChange($previous['revenue_lkr'], $current['revenue_lkr']);
        $ordersChange = $this->percentChange($previous['orders'], $current['orders']);

        $cards = [
            [
                'title' => 'Revenue',
                'value' => $this->formatLkr($current['revenue_lkr']),
                'change' => $this->formatChange($revenueChange),
                'change_direction' => $this->changeDirection($revenueChange),
                'icon' => 'bi-cash-stack',
                'detail' => ucwords(str_replace('_', ' ', $period)).' · paid orders only',
            ],
            [
                'title' => 'Orders',
                'value' => (string) $current['orders'],
                'change' => $this->formatChange($ordersChange),
                'change_direction' => $this->changeDirection($ordersChange),
                'icon' => 'bi-bag-check',
                'detail' => $current['paid_orders'].' paid · '.$current['pending_orders'].' pending',
            ],
            [
                'title' => 'New customers',
                'value' => (string) $current['new_customers'],
                'change' => null,
                'change_direction' => 'flat',
                'icon' => 'bi-people',
                'detail' => 'Registered in selected period',
            ],
            [
                'title' => 'Active products',
                'value' => (string) Product::active()->count(),
                'change' => null,
                'change_direction' => 'flat',
                'icon' => 'bi-box-seam',
                'detail' => Product::count().' total in catalog',
            ],
        ];

        $topProducts = collect($this->topProducts($period, 10))->map(fn ($row) => [
            'name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'revenue_lkr' => $row['revenue_lkr'],
        ])->all();

        $stockAlerts = $this->lowStockProducts(10);

        $dailyRevenue = $this->dailyRevenue($period);
        $forecast = $this->simpleForecast($dailyRevenue);

        return [
            'cards' => $cards,
            'recommendations' => $this->ruleBasedRecommendations($current, $stockAlerts, $topProducts),
            'top_products' => $topProducts,
            'stock_alerts' => $stockAlerts,
            'category_trends' => $this->categoryTrends($period),
            'customer_insights' => [
                'new_customers' => $current['new_customers'],
                'repeat_customer_orders' => $this->repeatCustomerOrders($period),
                'avg_order_value_lkr' => $current['avg_order_value_lkr'],
            ],
            'sales_forecast' => $forecast,
            'daily_revenue' => $dailyRevenue,
        ];
    }

    public function periodStats(string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $ordersQuery = Order::whereBetween('created_at', [$from, $to]);
        $orders = (clone $ordersQuery)->count();
        $paidOrders = (clone $ordersQuery)->where('payment_status', 'paid')->count();
        $pendingOrders = (clone $ordersQuery)->where('status', 'pending')->count();
        $revenue = (float) (clone $ordersQuery)->where('payment_status', 'paid')->sum('total');
        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();
        $avgOrder = $paidOrders > 0 ? round($revenue / $paidOrders, 2) : 0.0;

        return [
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'orders' => $orders,
            'paid_orders' => $paidOrders,
            'pending_orders' => $pendingOrders,
            'revenue_lkr' => round($revenue, 2),
            'new_customers' => $newCustomers,
            'avg_order_value_lkr' => $avgOrder,
        ];
    }

    private function previousPeriodStats(string $period): array
    {
        [$from, $to] = $this->periodRange($period);
        $days = max(1, $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        $ordersQuery = Order::whereBetween('created_at', [$prevFrom, $prevTo]);
        $orders = (clone $ordersQuery)->count();
        $revenue = (float) (clone $ordersQuery)->where('payment_status', 'paid')->sum('total');

        return [
            'orders' => $orders,
            'revenue_lkr' => round($revenue, 2),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfDay()],
            'last_week' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'last_7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'last_30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfDay()],
            default => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    private function topProducts(string $period, int $limit): array
    {
        [$from, $to] = $this->periodRange($period);

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('product_name, SUM(quantity) as quantity, SUM(total_price) as revenue_lkr')
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_name' => $row->product_name,
                'quantity' => (int) $row->quantity,
                'revenue_lkr' => round((float) $row->revenue_lkr, 2),
            ])
            ->all();
    }

    private function lowStockProducts(int $limit): array
    {
        return Product::query()
            ->where('is_active', true)
            ->where('manage_stock', true)
            ->where(function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->orWhere('stock_quantity', '<=', 5);
            })
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get(['name', 'sku', 'stock_quantity', 'low_stock_threshold'])
            ->map(fn (Product $p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'stock' => (int) $p->stock_quantity,
                'threshold' => (int) $p->low_stock_threshold,
                'risk_level' => ((int) $p->stock_quantity) <= 2 ? 'critical' : 'warning',
            ])
            ->all();
    }

    private function productsByCategory(int $limit): array
    {
        return Category::query()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn (Category $c) => [
                'category' => $c->name,
                'active_products' => (int) $c->products_count,
            ])
            ->all();
    }

    private function recentOrders(int $limit): array
    {
        return Order::query()
            ->with('customer:id,name')
            ->latest()
            ->limit($limit)
            ->get(['id', 'order_number', 'customer_id', 'total', 'payment_status', 'status', 'created_at'])
            ->map(fn (Order $o) => [
                'order_number' => $o->order_number,
                'customer' => $o->customer?->name,
                'total_lkr' => round((float) $o->total, 2),
                'payment_status' => $o->payment_status,
                'status' => $o->status,
                'date' => $o->created_at?->toDateString(),
            ])
            ->all();
    }

    private function dailyRevenue(string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        return Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue_lkr, COUNT(*) as orders')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'revenue_lkr' => round((float) $row->revenue_lkr, 2),
                'orders' => (int) $row->orders,
            ])
            ->all();
    }

    private function categoryTrends(string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('categories.name as category, SUM(order_items.quantity) as quantity, SUM(order_items.total_price) as revenue_lkr')
            ->groupBy('categories.name')
            ->orderByDesc('revenue_lkr')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'quantity' => (int) $row->quantity,
                'revenue_lkr' => round((float) $row->revenue_lkr, 2),
            ])
            ->all();
    }

    private function repeatCustomerOrders(string $period): int
    {
        [$from, $to] = $this->periodRange($period);

        return (int) Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('customer_id')
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
    }

    private function simpleForecast(array $dailyRevenue): array
    {
        if (count($dailyRevenue) < 2) {
            return [
                'next_7_days_lkr' => 0,
                'next_30_days_lkr' => 0,
                'note' => 'Not enough sales history for a reliable forecast.',
            ];
        }

        $avgDaily = collect($dailyRevenue)->avg('revenue_lkr') ?: 0;

        return [
            'next_7_days_lkr' => round($avgDaily * 7, 2),
            'next_30_days_lkr' => round($avgDaily * 30, 2),
            'avg_daily_revenue_lkr' => round($avgDaily, 2),
            'note' => 'Simple forecast based on average daily paid revenue in the selected period.',
        ];
    }

    private function ruleBasedRecommendations(array $current, array $stockAlerts, array $topProducts): array
    {
        $recs = [];

        if (! empty($stockAlerts)) {
            $recs[] = 'Restock low-inventory products: '.collect($stockAlerts)->take(3)->pluck('name')->implode(', ').'.';
        }

        if ($current['pending_orders'] > 0) {
            $recs[] = 'You have '.$current['pending_orders'].' pending orders — review and process them to improve delivery time.';
        }

        if (! empty($topProducts)) {
            $recs[] = 'Promote your best seller "'.$topProducts[0]['name'].'" on the homepage or in a WhatsApp campaign.';
        }

        if ($current['new_customers'] === 0 && $current['orders'] > 0) {
            $recs[] = 'Orders came from existing customers only this period — consider a new-customer coupon or social campaign.';
        }

        if ($current['revenue_lkr'] <= 0) {
            $recs[] = 'No paid revenue in this period — check unpaid orders and follow up with customers.';
        }

        return array_slice($recs, 0, 6);
    }

    private function percentChange(float|int $previous, float|int $current): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function formatChange(?float $change): ?string
    {
        if ($change === null) {
            return null;
        }

        return ($change >= 0 ? '+' : '').number_format($change, 1).'%';
    }

    private function changeDirection(?float $change): string
    {
        if ($change === null || abs($change) < 0.1) {
            return 'flat';
        }

        return $change > 0 ? 'up' : 'down';
    }

    private function formatLkr(float $amount): string
    {
        return 'Rs. '.number_format($amount, 2);
    }
}

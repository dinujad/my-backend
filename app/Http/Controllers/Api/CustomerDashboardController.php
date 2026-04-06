<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $customer = $user->customer;
        if (!$customer) {
            return response()->json([
                'total_orders' => 0,
                'processing_orders' => 0,
                'shipped_orders' => 0,
                'completed_orders' => 0,
                'total_spent' => 0,
                'pending_payments' => 0,
                'paid_amount' => 0,
            ]);
        }

        $orders = $customer->orders()->get();

        return response()->json([
            'total_orders' => $orders->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'total_spent' => $orders->where('payment_status', 'paid')->sum('total'),
            'pending_payments' => $orders->where('payment_status', '!=', 'paid')->sum('total'),
            'paid_amount' => $orders->where('payment_status', 'paid')->sum('total'),
            'customer_info' => $customer,
        ]);
    }

    public function orders(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->customer) return response()->json([]);

        $orders = $user->customer->orders()
            ->with(['items', 'statusHistory', 'shipments', 'invoices'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    public function orderDetails(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->customer) return response()->json(['message' => 'Not found'], 404);

        $order = $user->customer->orders()
            ->with(['items', 'statusHistory', 'shipments', 'invoices'])
            ->findOrFail($id);

        return response()->json($order);
    }

    public function invoices(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->customer) return response()->json([]);

        $orders = $user->customer->orders()->pluck('id');
        
        $invoices = \App\Models\Invoice::whereIn('order_id', $orders)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($invoices);
    }
}

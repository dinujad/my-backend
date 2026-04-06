<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with('customer')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('customer', 'items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        return view('admin.orders.create', compact('customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'status' => 'required|string',
            'payment_status' => 'required|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
        $discount = (float) ($data['discount_amount'] ?? 0);
        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $total = $subtotal - $discount + $shipping;

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'customer_id' => $data['customer_id'],
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'total' => $total,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'shipping_method' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $oldPaymentStatus = $order->payment_status;

        $statusChangedToShipped = ($data['status'] === 'shipped' && $oldStatus !== 'shipped');
        $statusChanged = ($data['status'] !== $oldStatus);
        $paymentStatusChanged = ($data['payment_status'] !== $oldPaymentStatus);

        $order->update([
            'status' => $data['status'],
            'payment_status' => $data['payment_status']
        ]);

        if ($data['status'] === 'shipped') {
            $order->shipments()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'shipping_method' => $data['shipping_method'] ?? 'Courier',
                    'tracking_number' => $data['tracking_number'] ?? null,
                    'shipped_at' => now(),
                ]
            );

            if ($statusChangedToShipped) {
                try {
                    app(\App\Services\WhatsAppService::class)->sendOrderShipped($order);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("WhatsApp shipment notification failed: " . $e->getMessage());
                }
            }
        } elseif ($statusChanged) {
            try {
                app(\App\Services\WhatsAppService::class)->sendOrderStatusUpdate($order, $data['status']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("WhatsApp status notification failed: " . $e->getMessage());
            }
        }

        if ($paymentStatusChanged) {
            try {
                app(\App\Services\WhatsAppService::class)->sendPaymentStatusUpdate($order, $data['payment_status']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("WhatsApp payment status notification failed: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Order status updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->items()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}

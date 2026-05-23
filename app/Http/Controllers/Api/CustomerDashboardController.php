<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\QuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $customer = $user->customer;
        if (! $customer) {
            return response()->json([
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => null,
                    'member_since' => $user->created_at?->toIso8601String(),
                ],
                'total_orders' => 0,
                'processing_orders' => 0,
                'shipped_orders' => 0,
                'completed_orders' => 0,
                'total_spent' => 0,
                'pending_payments' => 0,
                'paid_amount' => 0,
                'quote_counts' => ['total' => 0, 'open' => 0, 'quoted' => 0],
                'recent_orders' => [],
                'recent_quotes' => [],
            ]);
        }

        $orders = $customer->orders()->get();

        $quoteQuery = $this->quoteQueryForCustomer($customer);
        $quotes = (clone $quoteQuery)->get();

        $openStatuses = ['new', 'reviewing', 'awaiting_pricing', 'customer_responded'];
        $quotedStatuses = ['quoted', 'sent', 'approved'];

        return response()->json([
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'member_since' => $customer->created_at?->toIso8601String(),
            ],
            'total_orders' => $orders->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'total_spent' => (float) $orders->where('payment_status', 'paid')->sum('total'),
            'pending_payments' => (float) $orders->where('payment_status', '!=', 'paid')->sum('total'),
            'paid_amount' => (float) $orders->where('payment_status', 'paid')->sum('total'),
            'quote_counts' => [
                'total' => $quotes->count(),
                'open' => $quotes->whereIn('status', $openStatuses)->count(),
                'quoted' => $quotes->whereIn('status', $quotedStatuses)->count(),
            ],
            'recent_orders' => $customer->orders()
                ->with(['items', 'shipments'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Order $o) => $this->formatOrderSummary($o)),
            'recent_quotes' => (clone $quoteQuery)
                ->with(['items', 'quotation'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (QuoteRequest $q) => $this->formatQuoteSummary($q)),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $customer = $this->customerOrAbort($request);

        $orders = $customer->orders()
            ->with(['items', 'statusHistory', 'shipments', 'invoices'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $orders->getCollection()->transform(fn (Order $o) => $this->formatOrderSummary($o, true));

        return response()->json($orders);
    }

    public function orderDetails(Request $request, $id): JsonResponse
    {
        $customer = $this->customerOrAbort($request);

        $order = $customer->orders()
            ->with(['items', 'statusHistory', 'shipments', 'invoices', 'payments'])
            ->findOrFail($id);

        return response()->json($this->formatOrderDetail($order));
    }

    public function invoices(Request $request): JsonResponse
    {
        $customer = $this->customerOrAbort($request);

        $orderIds = $customer->orders()->pluck('id');

        $invoices = \App\Models\Invoice::whereIn('order_id', $orderIds)
            ->with('order')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($invoices);
    }

    public function quotes(Request $request): JsonResponse
    {
        $customer = $this->customerOrAbort($request);

        $quotes = $this->quoteQueryForCustomer($customer)
            ->with(['items', 'quotation'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $quotes->getCollection()->transform(fn (QuoteRequest $q) => $this->formatQuoteSummary($q, true));

        return response()->json($quotes);
    }

    public function quoteDetails(Request $request, $id): JsonResponse
    {
        $customer = $this->customerOrAbort($request);

        $quote = $this->quoteQueryForCustomer($customer)
            ->with(['items', 'quotation.items', 'statusLogs', 'quotation'])
            ->findOrFail($id);

        return response()->json($this->formatQuoteDetail($quote));
    }

    private function customerOrAbort(Request $request): Customer
    {
        $user = $request->user();
        if (! $user || ! $user->customer) {
            abort(404, 'Customer profile not found');
        }

        return $user->customer;
    }

    private function quoteQueryForCustomer(Customer $customer)
    {
        return QuoteRequest::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if ($customer->email) {
                    $q->orWhere('email', $customer->email);
                }
            });
    }

    private function formatOrderSummary(Order $order, bool $includeItems = false): array
    {
        $latestShipment = $order->relationLoaded('shipments')
            ? $order->shipments->sortByDesc('shipped_at')->first()
            : null;

        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'total' => (float) $order->total,
            'created_at' => $order->created_at?->toIso8601String(),
            'items_count' => $order->relationLoaded('items') ? $order->items->count() : null,
            'delivery' => [
                'status' => $order->status,
                'shipped_at' => $latestShipment?->shipped_at?->toIso8601String(),
                'delivered_at' => $order->status === 'completed'
                    ? ($latestShipment?->shipped_at?->toIso8601String() ?? $order->updated_at?->toIso8601String())
                    : null,
                'tracking_number' => $latestShipment?->tracking_number,
                'carrier' => $latestShipment?->shipping_method ?? $order->shipping_method,
            ],
        ];

        if ($includeItems && $order->relationLoaded('items')) {
            $data['items'] = $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'total_price' => (float) $item->total_price,
            ])->values();
        }

        return $data;
    }

    private function formatOrderDetail(Order $order): array
    {
        $summary = $this->formatOrderSummary($order, true);

        return array_merge($order->toArray(), $summary, [
            'shipments' => $order->shipments->map(fn ($s) => [
                'id' => $s->id,
                'shipping_method' => $s->shipping_method,
                'tracking_number' => $s->tracking_number,
                'shipping_notes' => $s->shipping_notes,
                'shipped_at' => $s->shipped_at?->toIso8601String(),
            ])->values(),
            'status_history' => $order->statusHistory->map(fn ($h) => [
                'status' => $h->status,
                'note' => $h->notes,
                'created_at' => $h->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    private function formatQuoteSummary(QuoteRequest $quote, bool $includeItems = false): array
    {
        $quotation = $quote->relationLoaded('quotation') ? $quote->quotation : null;
        $hasAdminQuote = $quotation && in_array($quote->status, ['quoted', 'sent', 'approved', 'customer_responded'], true);

        $data = [
            'id' => $quote->id,
            'request_number' => $quote->request_number,
            'status' => $quote->status,
            'status_label' => $quote->status_label,
            'urgency' => $quote->urgency,
            'deadline' => $quote->deadline?->format('Y-m-d'),
            'created_at' => $quote->created_at?->toIso8601String(),
            'items_count' => $quote->relationLoaded('items') ? $quote->items->count() : null,
            'has_admin_response' => $hasAdminQuote,
            'quotation_total' => $hasAdminQuote ? (float) $quotation->total : null,
            'quotation_status' => $hasAdminQuote ? $quotation->status : null,
            'view_quote_url' => $hasAdminQuote && $quotation->public_token
                ? '/quote/view/'.$quotation->public_token
                : null,
        ];

        if ($includeItems && $quote->relationLoaded('items')) {
            $data['items'] = $quote->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'product_sku' => $item->product_sku,
            ])->values();
        }

        return $data;
    }

    private function formatQuoteDetail(QuoteRequest $quote): array
    {
        $summary = $this->formatQuoteSummary($quote, true);
        $quotation = $quote->quotation;

        $adminResponse = null;
        if ($quotation && in_array($quote->status, ['quoted', 'sent', 'approved', 'customer_responded', 'rejected', 'closed'], true)) {
            $adminResponse = [
                'quote_number' => $quotation->quote_number,
                'status' => $quotation->status,
                'status_label' => $quotation->status_label,
                'total' => (float) $quotation->total,
                'valid_until' => $quotation->valid_until?->format('Y-m-d'),
                'sent_at' => $quotation->sent_at?->toIso8601String(),
                'notes' => $quotation->notes,
                'payment_terms' => $quotation->payment_terms,
                'delivery_details' => $quotation->delivery_details,
                'public_token' => $quotation->public_token,
                'view_url' => $quotation->public_token ? '/quote/view/'.$quotation->public_token : null,
                'items' => $quotation->relationLoaded('items')
                    ? $quotation->items->map(fn ($i) => [
                        'description' => $i->description,
                        'quantity' => $i->quantity,
                        'unit_price' => (float) $i->unit_price,
                        'total' => (float) ($i->subtotal ?? 0),
                    ])->values()
                    : [],
            ];
        }

        return array_merge($summary, [
            'customer_name' => $quote->customer_name,
            'company_name' => $quote->company_name,
            'email' => $quote->email,
            'phone' => $quote->phone,
            'address' => $quote->address,
            'customer_notes' => $quote->customer_notes,
            'admin_notes' => $quote->admin_notes,
            'preferred_contact' => $quote->preferred_contact,
            'status_timeline' => $quote->statusLogs->map(fn ($log) => [
                'from_status' => $log->from_status,
                'to_status' => $log->to_status,
                'to_label' => QuoteRequest::$statuses[$log->to_status] ?? $log->to_status,
                'note' => $log->note,
                'created_at' => $log->created_at?->toIso8601String(),
            ])->values(),
            'admin_response' => $adminResponse,
        ]);
    }
}

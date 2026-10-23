<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayzyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayzyController extends Controller
{
    public function __construct(private PayzyService $payzy) {}

    public function initiate(Request $request): JsonResponse
    {
        $request->validate(['order_number' => 'required|string']);

        $order = Order::where('order_number', $request->order_number)->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order already paid'], 409);
        }

        try {
            $checkout = $this->payzy->createCheckout($order);
        } catch (RuntimeException $e) {
            Log::error('Payzy initiate failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(['redirect_url' => $checkout['redirect_url']]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_number' => 'required|string',
            'response_code' => 'required|string',
            'signature' => 'required|string',
        ]);

        $order = Order::where('order_number', $data['order_number'])->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $paid = $this->payzy->confirm($order, $data['response_code'], $data['signature']);
        $order->refresh();

        return response()->json([
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'paid' => $paid,
        ]);
    }
}

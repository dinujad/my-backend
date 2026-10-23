<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OnepayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OnepayController extends Controller
{
    public function __construct(private OnepayService $onepay) {}

    /**
     * POST /api/payments/onepay/initiate
     * Creates a live OnePay checkout and returns the same-window redirect URL.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->order_number)->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order already paid'], 409);
        }

        try {
            $checkout = $this->onepay->createCheckout($order);
        } catch (RuntimeException $e) {
            Log::error('OnePay initiate failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'redirect_url' => $checkout['redirect_url'],
        ]);
    }

    /**
     * POST /api/onepay/callback
     * Portal callback. Payment is marked paid only after the status API confirms it.
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        if ($payload === []) {
            $payload = $request->all();
        }

        $headers = array_change_key_case($request->headers->all(), CASE_LOWER);
        $flat = [];
        foreach ($headers as $name => $values) {
            $flat[$name] = is_array($values) ? (string) ($values[0] ?? '') : (string) $values;
        }

        $provided = $this->onepay->providedCallbackToken($payload, $flat);
        if (! $this->onepay->callbackTokenMatches($provided)) {
            Log::warning('OnePay callback rejected');

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $this->onepay->handleCallback($payload);
        } catch (\Throwable $e) {
            Log::error('OnePay callback error: '.$e->getMessage());

            return response()->json(['message' => 'ERROR'], 500);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * GET /api/orders/{orderNumber}/payment-status
     * Return page polls this. A pending OnePay order is checked with the status API.
     */
    public function paymentStatus(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->payment_status !== 'paid') {
            $ipgId = (string) Payment::where('order_id', $order->id)
                ->where('payment_method', 'onepay')
                ->value('gateway_payment_id');

            if ($ipgId !== '') {
                try {
                    $this->onepay->confirm($order, $ipgId);
                    $order->refresh();
                } catch (\Throwable $e) {
                    Log::warning('OnePay status poll failed', ['order' => $orderNumber]);
                }
            }
        }

        return response()->json([
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
        ]);
    }
}

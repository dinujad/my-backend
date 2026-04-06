<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayhereService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayhereController extends Controller
{
    public function __construct(private PayhereService $payhere) {}

    /**
     * POST /api/payments/payhere/initiate
     * Called from frontend after order is created with payment_method=payhere.
     * Returns the checkout URL and signed params for form POST redirect.
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

        $address = $order->shipping_address ?? [];

        $params = $this->payhere->buildCheckoutParams($order, [
            'name'     => $address['first_name'] . ' ' . ($address['last_name'] ?? ''),
            'email'    => $address['email'] ?? '',
            'phone'    => $address['phone'] ?? '',
            'address'  => $address['line1'] ?? '',
            'district' => $address['district'] ?? '',
        ]);

        return response()->json($params);
    }

    /**
     * POST /api/payments/payhere/notify
     * Called by PayHere servers (webhook). Must be publicly accessible, no CSRF.
     * IMPORTANT: Validate signature before processing.
     */
    public function notify(Request $request): Response
    {
        $data = $request->all();

        Log::info('PayHere notify received', $data);

        try {
            if (! $this->payhere->verifyNotification($data)) {
                Log::warning('PayHere notify: signature mismatch', $data);
                return response('SIGNATURE_MISMATCH', 400);
            }

            $this->payhere->processNotification($data);

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('PayHere notify error: ' . $e->getMessage(), $data);
            return response('ERROR', 500);
        }
    }
}

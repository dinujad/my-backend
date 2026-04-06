<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use App\Services\EmailService;
use App\Services\SMSService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class CheckoutApiController extends Controller
{
    protected CheckoutService $checkoutService;
    protected EmailService $emailService;
    protected SMSService $smsService;
    protected PaymentService $paymentService;

    public function __construct(
        CheckoutService $checkoutService,
        EmailService $emailService,
        SMSService $smsService,
        PaymentService $paymentService
    ) {
        $this->checkoutService = $checkoutService;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
        $this->paymentService = $paymentService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_email' => 'required|email|max:255',
            'register' => 'boolean',
            'password' => 'required_if:register,true|string|min:8', // removed confirmed for safer API requests unless sent
            'items' => 'required|string', // JSON encoded array
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $order = $this->checkoutService->processCheckout($request);

            $user = $order->customer->user;
            $token = null;

            if ($request->boolean('register') && $user) {
                $token = $user->createToken('live-chat-auth-token')->plainTextToken;
                
                // Send Welcome Message
                try {
                    app(\App\Services\EmailService::class)->sendWelcomeEmail($user);

                    $phone = $order->shipping_address['phone'] ?? $order->customer->phone;
                    if ($phone) {
                        app(\App\Services\WhatsAppService::class)->sendWelcomeMessage($phone, $order->customer->name);
                    }
                } catch (Exception $e) {
                    Log::error("Failed to send welcome message during checkout for order {$order->id}: " . $e->getMessage());
                }
            }

            // Send emails/sms/whatsapp order confirmation
            try {
                $this->emailService->sendOrderEmail($order, 'created');
                $this->smsService->sendOrderSms($order, 'created');
                app(\App\Services\WhatsAppService::class)->sendOrderConfirmation($order);
            } catch (Exception $e) {
                Log::error("Failed to send checkout notifications for order {$order->id}: " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $request->input('payment_method', 'cod'),
                'token' => $token,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ], 201);

        } catch (Exception $e) {
            Log::error("Checkout failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

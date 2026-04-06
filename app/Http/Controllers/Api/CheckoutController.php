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

class CheckoutController extends Controller
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

    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address.line1' => 'required|string|max:255',
            'address.city' => 'required|string|max:255',
            'register' => 'boolean',
            'password' => 'required_if:register,true|string|min:8|confirmed',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $order = $this->checkoutService->processCheckout($request->all());

            // Handle payment logic if applicable (e.g. cash on delivery vs card). Assuming COD for now.
            // If it's a card payment, PaymentService should be triggered after payment gateway success.
            // $this->paymentService->processPayment($order, $order->total, 'cod');

            DB::commit();

            // Send emails/sms/whatsapp
            try {
                // If user registered, maybe send welcome email (not implemented here, could trigger event)
                $this->emailService->sendOrderEmail($order, 'created');
                $this->smsService->sendOrderSms($order, 'created');
                app(\App\Services\WhatsAppService::class)->sendOrderConfirmation($order);
            } catch (Exception $e) {
                Log::error("Failed to send checkout notifications for order {$order->id}: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'redirect_url' => '/checkout/success'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Checkout failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

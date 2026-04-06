<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PayhereSettings;
use Illuminate\Support\Facades\Log;

class PayhereService
{
    private PayhereSettings $settings;

    public function __construct()
    {
        $this->settings = PayhereSettings::instance();
    }

    /**
     * Generate the PayHere checkout hash (backend-only).
     *
     * hash = UPPERCASE(MD5(
     *   merchant_id + order_id + amount(2dp) + currency + UPPERCASE(MD5(merchant_secret))
     * ))
     */
    public function generateHash(string $orderId, float $amount, string $currency = 'LKR'): string
    {
        $merchantId     = $this->settings->getMerchantId();
        $merchantSecret = $this->settings->getMerchantSecret();

        $hashedSecret = strtoupper(md5($merchantSecret));
        $amountFormatted = number_format($amount, 2, '.', '');

        return strtoupper(
            md5($merchantId . $orderId . $amountFormatted . $currency . $hashedSecret)
        );
    }

    /**
     * Build the full PayHere form fields for frontend auto-submit.
     */
    public function buildCheckoutParams(Order $order, array $customerData): array
    {
        $merchantId  = $this->settings->getMerchantId();
        $checkoutUrl = $this->settings->getCheckoutUrl();
        $appUrl      = rtrim(config('app.url'), '/');
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');

        $amount      = (float) $order->total;
        $orderId     = $order->order_number;
        $hash        = $this->generateHash($orderId, $amount);

        $nameParts  = explode(' ', $customerData['name'] ?? '', 2);
        $firstName  = $nameParts[0] ?? '';
        $lastName   = $nameParts[1] ?? '';

        return [
            'checkout_url' => $checkoutUrl,
            'params'       => [
                'merchant_id'  => $merchantId,
                'return_url'   => $frontendUrl . '/checkout/payment/return?order=' . $orderId,
                'cancel_url'   => $frontendUrl . '/checkout/payment/cancel?order=' . $orderId,
                'notify_url'   => $appUrl . '/api/payments/payhere/notify',
                'order_id'     => $orderId,
                'items'        => 'Order ' . $orderId . ' — PrintWorksLK',
                'currency'     => 'LKR',
                'amount'       => number_format($amount, 2, '.', ''),
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'email'        => $customerData['email'] ?? '',
                'phone'        => $customerData['phone'] ?? '',
                'address'      => $customerData['address'] ?? '',
                'city'         => $customerData['district'] ?? $customerData['city'] ?? '',
                'country'      => 'Sri Lanka',
                'hash'         => $hash,
            ],
        ];
    }

    /**
     * Verify a PayHere notification callback.
     *
     * local_md5sig = MD5(merchant_id + order_id + payhere_amount + payhere_currency + status_code + MD5(merchant_secret))
     */
    public function verifyNotification(array $data): bool
    {
        $merchantSecret = $this->settings->getMerchantSecret();

        $localSig = strtoupper(md5(
            $data['merchant_id'] .
            $data['order_id'] .
            $data['payhere_amount'] .
            $data['payhere_currency'] .
            $data['status_code'] .
            strtoupper(md5($merchantSecret))
        ));

        return $localSig === strtoupper($data['md5sig'] ?? '');
    }

    /**
     * Process a verified notification (status_code == 2 = success).
     */
    public function processNotification(array $data): void
    {
        $orderNumber = $data['order_id'];
        $statusCode  = (int) ($data['status_code'] ?? 0);

        $order = \App\Models\Order::where('order_number', $orderNumber)->first();
        if (! $order) {
            Log::warning("PayHere notify: order not found [{$orderNumber}]");
            return;
        }

        // Idempotency: skip if already marked paid
        if ($order->payment_status === 'paid') {
            Log::info("PayHere notify: order [{$orderNumber}] already paid, skipping.");
            return;
        }

        $rawData = json_encode($data);

        if ($statusCode === 2) {
            // PAYMENT SUCCESS
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id, 'payment_method' => 'payhere'],
                [
                    'gateway_payment_id' => $data['payment_id'] ?? null,
                    'amount'             => (float) ($data['payhere_amount'] ?? $order->total),
                    'currency'           => $data['payhere_currency'] ?? 'LKR',
                    'status'             => 'completed',
                    'payment_details'    => $data['method'] ?? null,
                    'raw_response'       => $rawData,
                ]
            );

            $order->update([
                'payment_status' => 'paid',
                'status'         => $order->status === 'pending' ? 'processing' : $order->status,
            ]);

            $order->statusHistory()->create([
                'status'             => 'processing',
                'notes'              => 'PayHere payment confirmed. Payment ID: ' . ($data['payment_id'] ?? 'N/A'),
                'customer_notified'  => false,
            ]);

            Log::info("PayHere: Order [{$orderNumber}] marked paid.", ['payment_id' => $payment->id]);
        } else {
            // FAILED / PENDING / CANCELLED
            $statusMap = [
                '-1' => 'cancelled',
                '-2' => 'failed',
                '-3' => 'chargedback',
                '0'  => 'pending',
            ];

            $failStatus = $statusMap[(string) $statusCode] ?? 'failed';

            Payment::updateOrCreate(
                ['order_id' => $order->id, 'payment_method' => 'payhere'],
                [
                    'status'       => $failStatus,
                    'raw_response' => $rawData,
                ]
            );

            $order->update(['payment_status' => $failStatus === 'pending' ? 'unpaid' : 'failed']);

            Log::warning("PayHere notify: Order [{$orderNumber}] status [{$statusCode}] → [{$failStatus}]");
        }
    }

    public function getSettings(): PayhereSettings
    {
        return $this->settings;
    }
}

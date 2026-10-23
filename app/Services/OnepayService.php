<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OnepayService
{
    public static function hash(string $appId, string $currency, string $amount, string $salt): string
    {
        return hash('sha256', $appId.$currency.$amount.$salt);
    }

    public static function amount(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Sri Lankan numbers become E.164 (+9477...).
     */
    public static function phone(string $phone): string
    {
        $trimmed = trim($phone);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if (str_starts_with($digits, '94') && strlen($digits) >= 11) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '+94'.substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '+94'.$digits;
        }

        return str_starts_with($trimmed, '+') ? '+'.$digits : '+'.$digits;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{redirect_url: ?string, ipg_transaction_id: ?string}
     */
    public static function extractCheckout(array $body): array
    {
        $redirect = self::findString($body, 'redirect_url');
        $ipg = self::findString($body, 'ipg_transaction_id');

        return [
            'redirect_url' => $redirect,
            'ipg_transaction_id' => $ipg,
        ];
    }

    /**
     * Status API `status` is a boolean. A numeric HTTP-style status is not a payment result.
     *
     * @param  array<string, mixed>  $body
     */
    public static function isPaid(array $body): bool
    {
        if (array_key_exists('status', $body) && is_bool($body['status'])) {
            return $body['status'] === true;
        }

        $data = $body['data'] ?? null;
        if (is_array($data)) {
            return self::isPaid($data);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function chargedAmount(array $body): ?string
    {
        $amount = self::findValue($body, 'amount');
        if (is_array($amount)) {
            $amount = $amount['net_amount'] ?? $amount['gross_amount'] ?? null;
        }

        if ($amount === null || $amount === '' || is_array($amount)) {
            return null;
        }

        return self::amount((string) $amount);
    }

    public static function redirectHostAllowed(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return false;
        }

        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            return false;
        }

        $host = strtolower($host);

        return $host === 'onepay.lk' || str_ends_with($host, '.onepay.lk');
    }

    /**
     * @return array{redirect_url: string, ipg_transaction_id: ?string}
     */
    public function createCheckout(Order $order): array
    {
        $appId = (string) config('onepay.app_id');
        $token = (string) config('onepay.app_token');
        $salt = (string) config('onepay.hash_salt');
        $currency = (string) config('onepay.currency', 'LKR');

        if ($appId === '' || $token === '' || $salt === '') {
            throw new RuntimeException('OnePay is not configured. Set ONEPAY_APP_ID, ONEPAY_APP_TOKEN, and ONEPAY_HASH_SALT.');
        }

        $frontend = rtrim((string) config('app.frontend_url'), '/');
        if (! str_starts_with($frontend, 'https://')) {
            throw new RuntimeException('OnePay return URL must be HTTPS. Set FRONTEND_URL to the live storefront.');
        }

        $address = is_array($order->shipping_address) ? $order->shipping_address : [];
        $first = trim((string) ($address['first_name'] ?? ''));
        $last = trim((string) ($address['last_name'] ?? ''));
        if ($first === '') {
            $first = 'Customer';
        }
        if ($last === '') {
            $last = 'Customer';
        }

        $amount = self::amount((float) $order->total);
        $reference = (string) $order->order_number;
        $payload = [
            'app_id' => $appId,
            'amount' => $amount,
            'currency' => $currency,
            'hash' => self::hash($appId, $currency, $amount, $salt),
            'reference' => $reference,
            'customer_first_name' => $first,
            'customer_last_name' => $last,
            'customer_phone_number' => self::phone((string) ($address['phone'] ?? '')),
            'customer_email' => (string) ($address['email'] ?? ''),
            'transaction_redirect_url' => $frontend.'/checkout/payment/return?order='.rawurlencode($reference),
            'additionalData' => $reference,
        ];

        $response = $this->post('/v3/checkout/link/', $payload);
        $body = $response->json();
        if (! is_array($body)) {
            throw new RuntimeException('OnePay checkout did not return JSON.');
        }

        $checkout = self::extractCheckout($body);
        $redirect = $checkout['redirect_url'];
        if (! $response->successful() || ! is_string($redirect) || ! str_starts_with($redirect, 'https://') || ! self::redirectHostAllowed($redirect)) {
            $message = is_array($body) && is_string($body['message'] ?? null)
                ? $body['message']
                : 'OnePay did not return a payment page URL.';
            Log::warning('OnePay checkout response missing a usable redirect', [
                'order' => $reference,
                'http_status' => $response->status(),
            ]);
            throw new RuntimeException($message);
        }

        $methodId = PaymentMethod::where('code', 'onepay')->value('id');
        Payment::updateOrCreate(
            ['order_id' => $order->id, 'payment_method' => 'onepay'],
            [
                'payment_method_id' => $methodId,
                'gateway_payment_id' => $checkout['ipg_transaction_id'],
                'transaction_id' => $checkout['ipg_transaction_id'],
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_details' => 'OnePay',
                'raw_response' => json_encode([
                    'ipg_transaction_id' => $checkout['ipg_transaction_id'],
                ]),
            ]
        );

        return [
            'redirect_url' => $redirect,
            'ipg_transaction_id' => $checkout['ipg_transaction_id'],
        ];
    }

    /**
     * Confirm a payment with the status API. The callback body alone is not trusted.
     */
    public function confirm(Order $order, string $ipgTransactionId): bool
    {
        if ($order->payment_status === 'paid') {
            return true;
        }

        $appId = (string) config('onepay.app_id');
        if ($appId === '' || $ipgTransactionId === '') {
            return false;
        }

        $response = $this->post('/v3/transaction/status/', [
            'app_id' => $appId,
            'onepay_transaction_id' => $ipgTransactionId,
        ]);

        $body = $response->json();
        if (! is_array($body) || ! $response->successful()) {
            Log::warning('OnePay status check failed', [
                'order' => $order->order_number,
                'http_status' => $response->status(),
            ]);

            return false;
        }

        if (! self::isPaid($body)) {
            return false;
        }

        $charged = self::chargedAmount($body);
        $expected = self::amount((float) $order->total);
        if ($charged !== null && $charged !== $expected) {
            Log::warning('OnePay amount mismatch', [
                'order' => $order->order_number,
                'expected' => $expected,
                'charged' => $charged,
            ]);

            return false;
        }

        $methodId = PaymentMethod::where('code', 'onepay')->value('id');
        Payment::updateOrCreate(
            ['order_id' => $order->id, 'payment_method' => 'onepay'],
            [
                'payment_method_id' => $methodId,
                'gateway_payment_id' => $ipgTransactionId,
                'transaction_id' => $ipgTransactionId,
                'amount' => $expected,
                'currency' => (string) (self::findString($body, 'currency') ?: config('onepay.currency', 'LKR')),
                'status' => 'completed',
                'payment_details' => 'OnePay',
                'raw_response' => json_encode([
                    'ipg_transaction_id' => self::findString($body, 'ipg_transaction_id') ?: $ipgTransactionId,
                    'paid_on' => self::findString($body, 'paid_on'),
                ]),
            ]
        );

        $order->update([
            'payment_status' => 'paid',
            'status' => $order->status === 'pending' ? 'processing' : $order->status,
        ]);

        $order->statusHistory()->create([
            'status' => 'processing',
            'notes' => 'OnePay payment confirmed. Transaction: '.$ipgTransactionId,
            'customer_notified' => false,
        ]);

        app(OrderConfirmationNotifier::class)->send($order->fresh() ?? $order);

        Log::info('OnePay order marked paid', ['order' => $order->order_number]);

        return true;
    }

    public function callbackTokenMatches(?string $provided): bool
    {
        $expected = (string) config('onepay.callback_token');
        if ($expected === '' || $provided === null || $provided === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function providedCallbackToken(array $payload, array $headers): ?string
    {
        foreach (['x-callback-token', 'callback-token'] as $name) {
            $value = $headers[$name] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        $authorization = $headers['authorization'] ?? null;
        if (is_string($authorization) && $authorization !== '') {
            return trim((string) preg_replace('/^Bearer\s+/i', '', $authorization));
        }

        $bodyToken = $payload['callback_token'] ?? null;

        return is_string($bodyToken) && $bodyToken !== '' ? $bodyToken : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleCallback(array $payload): void
    {
        $transactionId = trim((string) ($payload['transaction_id'] ?? ''));
        $reference = trim((string) ($payload['additional_data'] ?? ''));

        $order = null;
        if ($reference !== '') {
            $order = Order::where('order_number', $reference)->first();
        }

        $payment = null;
        if (! $order && $transactionId !== '') {
            $payment = Payment::where('payment_method', 'onepay')
                ->where('gateway_payment_id', $transactionId)
                ->first();
            $order = $payment?->order;
        }

        if (! $order) {
            Log::warning('OnePay callback: order not found', [
                'reference' => $reference !== '' ? $reference : null,
            ]);

            return;
        }

        if ($order->payment_status === 'paid') {
            return;
        }

        $ipgId = $transactionId !== ''
            ? $transactionId
            : (string) ($payment?->gateway_payment_id ?? Payment::where('order_id', $order->id)->where('payment_method', 'onepay')->value('gateway_payment_id'));

        if ($ipgId !== '' && $this->confirm($order, $ipgId)) {
            return;
        }

        $message = strtoupper((string) ($payload['status_message'] ?? ''));
        if (in_array($message, ['FAILED', 'CANCELLED', 'CANCELED'], true)) {
            Payment::updateOrCreate(
                ['order_id' => $order->id, 'payment_method' => 'onepay'],
                [
                    'status' => 'failed',
                    'raw_response' => json_encode([
                        'status_message' => $message,
                    ]),
                ]
            );
            $order->update(['payment_status' => 'failed']);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function post(string $path, array $payload): Response
    {
        $token = (string) config('onepay.app_token');
        $url = rtrim((string) config('onepay.base_url'), '/').$path;

        return Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->timeout(15)->acceptJson()->asJson()->post($url, $payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function findString(array $data, string $key): ?string
    {
        $value = self::findValue($data, $key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function findValue(array $data, string $key): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $found = self::findValue($value, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

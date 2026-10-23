<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayzyService
{
    /** @var list<string> */
    public const REQUEST_FIELDS = [
        'x_test_mode',
        'x_shopid',
        'x_amount',
        'x_order_id',
        'x_response_url',
        'x_first_name',
        'x_last_name',
        'x_company',
        'x_address',
        'x_country',
        'x_state',
        'x_city',
        'x_zip',
        'x_phone',
        'x_email',
        'x_ship_to_first_name',
        'x_ship_to_last_name',
        'x_ship_to_company',
        'x_ship_to_address',
        'x_ship_to_country',
        'x_ship_to_state',
        'x_ship_to_city',
        'x_ship_to_zip',
        'x_freight',
        'x_platform',
        'x_version',
    ];

    public static function money(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    public static function goodsCharge(float $goods): string
    {
        $rate = (float) config('payzy.fee_rate', 0.12);

        return self::money($goods * (1 + $rate));
    }

    public static function sign(string $payload, string $secret): string
    {
        return base64_encode(hash_hmac('sha256', $payload, $secret, true));
    }

    /**
     * @param  array<string, string>  $fields
     */
    public static function requestString(array $fields): string
    {
        $signed = implode(',', [...self::REQUEST_FIELDS, 'signed_field_names']);
        $parts = [];
        foreach (self::REQUEST_FIELDS as $name) {
            $parts[] = $name.'='.($fields[$name] ?? '');
        }
        $parts[] = 'signed_field_names='.$signed;

        return implode(',', $parts);
    }

    /**
     * @param  array<string, string>  $fields
     */
    public static function verificationString(array $fields, string $responseCode): string
    {
        $order = [
            'x_test_mode', 'x_shopid', 'x_amount', 'x_order_id', 'x_response_url',
            'x_first_name', 'x_last_name', 'x_company', 'x_address', 'x_country',
            'x_state', 'x_city', 'x_zip', 'x_phone', 'x_email',
            'x_ship_to_first_name', 'x_ship_to_last_name', 'x_ship_to_company',
            'x_ship_to_address', 'x_ship_to_country', 'x_ship_to_state',
            'x_ship_to_city', 'x_ship_to_zip', 'x_freight', 'x_platform', 'x_version',
        ];
        $signed = 'response_code,'.implode(',', array_values(array_filter(
            $order,
            fn (string $name) => $name !== 'x_version'
        ))).',signed_field_names';

        $parts = ['response_code='.$responseCode];
        foreach ($order as $name) {
            $parts[] = $name.'='.($fields[$name] ?? '');
        }
        $parts[] = 'signed_field_names='.$signed;

        return implode(',', $parts);
    }

    public static function signaturesMatch(string $expected, string $provided): bool
    {
        $normalized = str_replace(' ', '+', trim($provided));

        return hash_equals($expected, $normalized);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function extractRedirect(array $body): ?string
    {
        $url = self::findString($body, 'url');

        return is_string($url) && str_starts_with($url, 'https://') ? $url : null;
    }

    public static function redirectHostAllowed(string $url): bool
    {
        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            return false;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === 'payzy.lk' || str_ends_with($host, '.payzy.lk');
    }

    /**
     * @return array{redirect_url: string}
     */
    public function createCheckout(Order $order): array
    {
        $shopId = (string) config('payzy.shop_id');
        $secret = (string) config('payzy.secret_key');
        if ($shopId === '' || $secret === '') {
            throw new RuntimeException('Payzy is not configured. Set PAYZY_SHOP_ID and PAYZY_SECRET_KEY.');
        }

        $frontend = rtrim((string) config('app.frontend_url'), '/');
        if (! str_starts_with($frontend, 'https://')) {
            throw new RuntimeException('Payzy return URL must be HTTPS. Set FRONTEND_URL to the live storefront.');
        }

        $fields = $this->fieldsFor($order, $frontend);
        $signature = self::sign(self::requestString($fields), $secret);
        $payload = $fields;
        $payload['signed_field_names'] = implode(',', [...self::REQUEST_FIELDS, 'signed_field_names']);
        $payload['signature'] = $signature;

        $response = Http::acceptJson()->asJson()->timeout(20)->post(
            rtrim((string) config('payzy.base_url'), '/').'/checkout/custom-checkout',
            $payload
        );
        $body = $response->json();
        if (! is_array($body)) {
            throw new RuntimeException('Payzy checkout did not return JSON.');
        }

        $redirect = self::extractRedirect($body);
        if (! $response->successful() || ! is_string($redirect) || ! self::redirectHostAllowed($redirect)) {
            Log::warning('Payzy checkout response missing a usable redirect', [
                'order' => $order->order_number,
                'http_status' => $response->status(),
            ]);
            $message = is_string($body['message'] ?? null) ? $body['message'] : 'Payzy did not return a payment page URL.';
            throw new RuntimeException($message);
        }

        $methodId = PaymentMethod::where('code', 'payzy')->value('id');
        Payment::updateOrCreate(
            ['order_id' => $order->id, 'payment_method' => 'payzy'],
            [
                'payment_method_id' => $methodId,
                'amount' => $fields['x_amount'],
                'currency' => 'LKR',
                'status' => 'pending',
                'payment_details' => 'Payzy',
                'raw_response' => json_encode(['fields' => $fields]),
            ]
        );

        return ['redirect_url' => $redirect];
    }

    public function confirm(Order $order, string $responseCode, string $signature): bool
    {
        if ($order->payment_status === 'paid') {
            return true;
        }

        $secret = (string) config('payzy.secret_key');
        $payment = Payment::where('order_id', $order->id)->where('payment_method', 'payzy')->first();
        $saved = json_decode((string) ($payment?->raw_response ?? ''), true);
        $fields = is_array($saved['fields'] ?? null) ? $saved['fields'] : null;
        if (! is_array($fields) || $secret === '') {
            return false;
        }

        $expected = self::sign(self::verificationString($fields, $responseCode), $secret);
        if (! self::signaturesMatch($expected, $signature)) {
            Log::warning('Payzy signature mismatch', ['order' => $order->order_number]);

            return false;
        }

        if ($responseCode !== '00') {
            Payment::updateOrCreate(
                ['order_id' => $order->id, 'payment_method' => 'payzy'],
                ['status' => 'failed', 'raw_response' => json_encode(['fields' => $fields, 'response_code' => $responseCode])]
            );
            $order->update(['payment_status' => 'failed']);

            return false;
        }

        $methodId = PaymentMethod::where('code', 'payzy')->value('id');
        Payment::updateOrCreate(
            ['order_id' => $order->id, 'payment_method' => 'payzy'],
            [
                'payment_method_id' => $methodId,
                'amount' => $fields['x_amount'] ?? $order->total,
                'currency' => 'LKR',
                'status' => 'completed',
                'payment_details' => 'Payzy',
                'raw_response' => json_encode(['fields' => $fields, 'response_code' => $responseCode]),
            ]
        );

        $order->update([
            'payment_status' => 'paid',
            'status' => $order->status === 'pending' ? 'processing' : $order->status,
        ]);
        $order->statusHistory()->create([
            'status' => 'processing',
            'notes' => 'Payzy payment confirmed.',
            'customer_notified' => false,
        ]);

        app(OrderConfirmationNotifier::class)->send($order->fresh() ?? $order);

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function fieldsFor(Order $order, string $frontend): array
    {
        $address = is_array($order->shipping_address) ? $order->shipping_address : [];
        $first = $this->clean((string) ($address['first_name'] ?? ''), 'Customer');
        $last = $this->clean((string) ($address['last_name'] ?? ''), 'Customer');
        $line = $this->clean((string) ($address['line1'] ?? ''), 'Sri Lanka');
        $district = $this->clean((string) ($address['district'] ?? $address['city'] ?? ''), 'Colombo');
        $zip = $this->clean((string) ($address['postal_code'] ?? ''), '00000');
        $company = $this->clean((string) config('payzy.company'), 'Print Works.LK');
        $goods = max(0, (float) $order->subtotal - (float) $order->discount_amount);
        $freight = max(0, (float) $order->shipping_cost);

        return [
            'x_test_mode' => (string) config('payzy.test_mode', 'off'),
            'x_shopid' => (string) config('payzy.shop_id'),
            'x_amount' => self::goodsCharge($goods),
            // 12% is added on the amount Payzy collects. The storefront total stays the selling price.
            'x_order_id' => (string) $order->order_number,
            'x_response_url' => $frontend.'/checkout/payment/return?order='.rawurlencode((string) $order->order_number),
            'x_first_name' => $first,
            'x_last_name' => $last,
            'x_company' => $company,
            'x_address' => $line,
            'x_country' => 'Sri Lanka',
            'x_state' => $district,
            'x_city' => $district,
            'x_zip' => $zip,
            'x_phone' => $this->clean((string) ($address['phone'] ?? ''), '0000000000'),
            'x_email' => $this->clean((string) ($address['email'] ?? ''), 'sales@printworks.lk'),
            'x_ship_to_first_name' => $first,
            'x_ship_to_last_name' => $last,
            'x_ship_to_company' => $company,
            'x_ship_to_address' => $line,
            'x_ship_to_country' => 'Sri Lanka',
            'x_ship_to_state' => $district,
            'x_ship_to_city' => $district,
            'x_ship_to_zip' => $zip,
            'x_freight' => self::goodsCharge($freight),
            'x_platform' => (string) config('payzy.platform', 'custom'),
            'x_version' => (string) config('payzy.version', '1.0'),
        ];
    }

    private function clean(string $value, string $fallback): string
    {
        $value = trim(str_replace([',', "\r", "\n"], ' ', $value));

        return $value !== '' ? $value : $fallback;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function findString(array $data, string $key): ?string
    {
        if (array_key_exists($key, $data) && is_string($data[$key]) && $data[$key] !== '') {
            return $data[$key];
        }
        foreach ($data as $value) {
            if (is_array($value)) {
                $found = self::findString($value, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

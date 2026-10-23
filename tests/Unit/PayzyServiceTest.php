<?php

namespace Tests\Unit;

use App\Services\PayzyService;
use PHPUnit\Framework\TestCase;

class PayzyServiceTest extends TestCase
{
    public function test_request_string_keeps_payzy_field_order(): void
    {
        $fields = [];
        foreach (PayzyService::REQUEST_FIELDS as $name) {
            $fields[$name] = $name === 'x_version' ? '1.0' : 'v';
        }

        $signed = implode(',', [...PayzyService::REQUEST_FIELDS, 'signed_field_names']);
        $string = PayzyService::requestString($fields);

        $this->assertStringStartsWith('x_test_mode=v,x_shopid=v,x_amount=v,', $string);
        $this->assertStringContainsString('x_version=1.0,signed_field_names='.$signed, $string);
    }

    public function test_signature_is_hmac_sha256_base64(): void
    {
        $payload = 'x_amount=100.00';
        $this->assertSame(
            base64_encode(hash_hmac('sha256', $payload, 'shop-secret', true)),
            PayzyService::sign($payload, 'shop-secret')
        );
    }

    public function test_verification_string_starts_with_response_code(): void
    {
        $string = PayzyService::verificationString([
            'x_test_mode' => 'off',
            'x_shopid' => '1673',
            'x_amount' => '112.00',
            'x_version' => '1.0',
            'x_platform' => 'custom',
        ], '00');

        $this->assertStringStartsWith('response_code=00,x_test_mode=off,x_shopid=1673,', $string);
        $this->assertStringContainsString('x_version=1.0,signed_field_names=response_code,', $string);
        $this->assertStringNotContainsString('signed_field_names=response_code,x_test_mode,x_shopid,x_amount,x_order_id,x_response_url,x_first_name,x_last_name,x_company,x_address,x_country,x_state,x_city,x_zip,x_phone,x_email,x_ship_to_first_name,x_ship_to_last_name,x_ship_to_company,x_ship_to_address,x_ship_to_country,x_ship_to_state,x_ship_to_city,x_ship_to_zip,x_freight,x_platform,x_version,', $string);
    }

    public function test_query_signature_spaces_are_restored_to_plus(): void
    {
        $expected = PayzyService::sign('payload', 'secret');
        $fromQuery = str_replace('+', ' ', $expected);

        $this->assertTrue(PayzyService::signaturesMatch($expected, $fromQuery));
        $this->assertFalse(PayzyService::signaturesMatch($expected, 'not-the-signature'));
    }

    public function test_redirect_must_be_payzy_https(): void
    {
        $this->assertTrue(PayzyService::redirectHostAllowed('https://checkout.payzy.lk/pay/1'));
        $this->assertFalse(PayzyService::redirectHostAllowed('https://evil.example/pay'));
        $this->assertFalse(PayzyService::redirectHostAllowed('http://payzy.lk/pay'));
    }
}

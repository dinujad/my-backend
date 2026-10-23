<?php

namespace Tests\Unit;

use App\Services\OnepayService;
use PHPUnit\Framework\TestCase;

class OnepayServiceTest extends TestCase
{
    public function test_hash_matches_onepay_example(): void
    {
        $hash = OnepayService::hash(
            '80NR1189D04CD635D8ACD',
            'LKR',
            '100.00',
            'XXXHASHSALTXXX'
        );

        $this->assertSame(
            hash('sha256', '80NR1189D04CD635D8ACDLKR100.00XXXHASHSALTXXX'),
            $hash
        );
    }

    public function test_amount_keeps_two_decimals(): void
    {
        $this->assertSame('100.00', OnepayService::amount(100));
        $this->assertSame('1499.50', OnepayService::amount('1499.5'));
    }

    public function test_phone_becomes_e164(): void
    {
        $this->assertSame('+94771234567', OnepayService::phone('0771234567'));
        $this->assertSame('+94771234567', OnepayService::phone('+94771234567'));
        $this->assertSame('+94771234567', OnepayService::phone('771234567'));
    }

    public function test_checkout_fields_are_read_from_nested_response(): void
    {
        $parsed = OnepayService::extractCheckout([
            'data' => [
                'ipg_transaction_id' => 'WQBV118E584C83CBA50C6',
                'gateway' => [
                    'redirect_url' => 'https://gateway.onepay.lk/pay/abc',
                ],
            ],
        ]);

        $this->assertSame('https://gateway.onepay.lk/pay/abc', $parsed['redirect_url']);
        $this->assertSame('WQBV118E584C83CBA50C6', $parsed['ipg_transaction_id']);
    }

    public function test_only_boolean_true_counts_as_paid(): void
    {
        $this->assertTrue(OnepayService::isPaid(['status' => true]));
        $this->assertFalse(OnepayService::isPaid(['status' => 1]));
        $this->assertFalse(OnepayService::isPaid(['status' => false]));
        $this->assertTrue(OnepayService::isPaid(['data' => ['status' => true]]));
    }

    public function test_redirect_host_must_be_onepay(): void
    {
        $this->assertTrue(OnepayService::redirectHostAllowed('https://gateway.onepay.lk/pay/abc'));
        $this->assertFalse(OnepayService::redirectHostAllowed('https://evil.example/pay'));
        $this->assertFalse(OnepayService::redirectHostAllowed('http://onepay.lk/pay'));
    }
}

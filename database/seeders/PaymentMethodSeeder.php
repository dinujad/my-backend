<?php

namespace Database\Seeders;

use App\Models\PayhereSettings;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code'        => 'cod',
                'name'        => 'Cash on Delivery',
                'description' => 'Pay when your order is delivered. Available island-wide.',
                'type'        => 'offline',
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'code'        => 'payhere',
                'name'        => 'Pay Online (PayHere)',
                'description' => 'Secure online payment via Visa, Mastercard, or local bank cards.',
                'type'        => 'online',
                'is_active'   => true,
                'sort_order'  => 2,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(['code' => $method['code']], $method);
        }

        // Create default PayHere settings row (sandbox mode, no credentials)
        PayhereSettings::firstOrCreate([], ['mode' => 'sandbox']);

        $this->command->info('Default payment methods (COD + PayHere) seeded.');
    }
}

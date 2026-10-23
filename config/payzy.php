<?php

return [
    'base_url' => rtrim((string) env('PAYZY_BASE_URL', 'https://api.payzy.lk'), '/'),
    'shop_id' => env('PAYZY_SHOP_ID'),
    'secret_key' => env('PAYZY_SECRET_KEY'),
    'test_mode' => env('PAYZY_TEST_MODE', 'off') === 'on' ? 'on' : 'off',
    'fee_rate' => (float) env('PAYZY_FEE_RATE', 0.12),
    'company' => env('PAYZY_COMPANY', 'Print Works.LK'),
    'version' => '1.0',
    'platform' => 'custom',
];

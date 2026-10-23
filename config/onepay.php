<?php

return [
    // Live gateway. There is no sandbox host in this integration.
    'base_url' => rtrim((string) env('ONEPAY_BASE_URL', 'https://api.onepay.lk'), '/'),

    'app_id' => env('ONEPAY_APP_ID'),
    'app_token' => env('ONEPAY_APP_TOKEN'),
    'hash_salt' => env('ONEPAY_HASH_SALT'),
    'callback_token' => env('ONEPAY_CALLBACK_TOKEN'),

    'currency' => env('ONEPAY_CURRENCY', 'LKR'),
];

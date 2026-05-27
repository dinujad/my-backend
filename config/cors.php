<?php

$parseOrigins = static function (string $value): array {
    return array_values(array_unique(array_filter(array_map(
        static fn (string $origin) => rtrim(trim($origin), '/'),
        explode(',', $value)
    ))));
};

$origins = $parseOrigins((string) env('CORS_ALLOWED_ORIGINS', ''));

if ($frontend = env('FRONTEND_URL')) {
    $origins = array_values(array_unique(array_merge(
        [rtrim((string) $frontend, '/')],
        $origins
    )));
}

if ($origins === []) {
    $origins = ['http://localhost:3000'];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Allowed origins are built from FRONTEND_URL plus optional CORS_ALLOWED_ORIGINS
    | (comma-separated). Set FRONTEND_URL in Coolify — no code changes per deploy.
    |
    | Example (production):
    |   APP_URL=https://api.printworks.lk
    |   FRONTEND_URL=https://printworks.lk
    |   CORS_ALLOWED_ORIGINS=https://www.printworks.lk
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Gemini API
    |--------------------------------------------------------------------------
    |
    | Admin AI chat uses Gemini directly from Laravel (no external Python service).
    | Set GEMINI_API_KEY in .env when you are ready to enable live AI responses.
    |
    */
    'gemini_api_key' => env('GEMINI_API_KEY'),

    'gemini_model' => env('GEMINI_MODEL', 'gemini-flash-latest'),

    'gemini_base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout_seconds' => (int) env('AI_SERVICE_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Store name shown in AI prompts
    |--------------------------------------------------------------------------
    */
    'store_name' => env('AI_STORE_NAME', 'Print Works.LK'),
];

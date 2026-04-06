<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service (FastAPI) URL
    |--------------------------------------------------------------------------
    |
    | Laravel calls the Python FastAPI service over HTTP.
    |
    */
    'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    // Gemini calls can be slower (especially when quota fallback kicks in),
    // so keep timeout higher to avoid "AI request failed" in the chat UI.
    'timeout_seconds' => (int) env('AI_SERVICE_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | HTTP Retry Count
    |--------------------------------------------------------------------------
    */
    // Avoid long overall wait: we already do model fallback inside FastAPI.
    'retry_count' => (int) env('AI_SERVICE_RETRY_COUNT', 0),
];


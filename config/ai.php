<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LLM provider order (comma-separated)
    |--------------------------------------------------------------------------
    |
    | First provider is tried first. On quota/rate-limit/error, next provider runs.
    | Example: gemini,claude  → try Gemini, then Claude (Anthropic).
    |
    */
    'providers' => env('AI_LLM_PROVIDERS', 'gemini,claude'),

    /*
    |--------------------------------------------------------------------------
    | Google Gemini API
    |--------------------------------------------------------------------------
    */
    'gemini_api_key' => env('GEMINI_API_KEY'),

    /*
    | Free-tier friendly models (lowest quota usage first):
    | - gemini-2.5-flash-lite   ← best for free tier (~15 RPM, more daily requests)
    | - gemini-2.5-flash
    | - gemini-2.0-flash
    | Avoid gemini-flash-latest → often resolves to gemini-3.5-flash (~20 RPM, hits quota fast)
    */
    'gemini_model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),

    'gemini_model_fallback' => env('GEMINI_MODEL_FALLBACK', 'gemini-2.5-flash-lite'),

    'gemini_base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
    |--------------------------------------------------------------------------
    | Anthropic Claude API (fallback when Gemini fails / rate limited)
    |--------------------------------------------------------------------------
    */
    'anthropic_api_key' => env('ANTHROPIC_API_KEY'),

    'claude_model' => env('CLAUDE_MODEL', 'claude-3-5-haiku-20241022'),

    'anthropic_base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),

    'anthropic_version' => env('ANTHROPIC_VERSION', '2023-06-01'),

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

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
    'providers' => env('AI_LLM_PROVIDERS', 'claude,gemini'),

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

    /*
    | Primary model (single). For multi-model fallback use CLAUDE_MODELS (comma-separated).
    | Order: best → cheapest. Each is tried in sequence; on any error the next is used.
    */
    'claude_model'  => env('CLAUDE_MODEL', 'claude-sonnet-4-5'),
    'claude_models' => env('CLAUDE_MODELS', 'claude-sonnet-4-5,claude-haiku-4-5,claude-sonnet-4-6,gpt-5.5,gpt-5.4,gemini-3.5-flash'),

    /*
    | Base URL for Claude. Set to an OpenAI-compatible proxy (e.g. api.ai.kodekloud.com/v1)
    | or leave as api.anthropic.com for the native Anthropic API.
    */
    'anthropic_base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),

    /*
    | API format: "auto" (detected from base URL), "openai" (OpenAI-compatible /chat/completions),
    | or "anthropic" (native /v1/messages). Set to "openai" for proxy endpoints.
    */
    'claude_api_format' => env('CLAUDE_API_FORMAT', 'auto'),

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
    'store_name'  => env('AI_STORE_NAME',  'Print Works.LK'),
    'store_phone' => env('AI_STORE_PHONE', '070 666 8885'),
    'store_email' => env('AI_STORE_EMAIL', 'sales@printworks.lk'),
    'store_url'   => env('AI_STORE_URL',   'https://printworks.lk'),
];

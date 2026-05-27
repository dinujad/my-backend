<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Tries LLM providers in order (default: gemini → claude).
 * Use when Gemini quota/rate-limit/key issues occur.
 */
class LlmGatewayService
{
    public function __construct(
        private GeminiService $gemini,
        private ClaudeService $claude,
    ) {}

    public function isConfigured(): bool
    {
        return $this->gemini->isConfigured() || $this->claude->isConfigured();
    }

    /**
     * @return list<string>
     */
    public function configuredProviders(): array
    {
        $out = [];
        if ($this->gemini->isConfigured()) {
            $out[] = 'gemini';
        }
        if ($this->claude->isConfigured()) {
            $out[] = 'claude';
        }

        return $out;
    }

    public function generate(
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens = 1024,
        float $temperature = 0.2,
        bool $jsonMode = false
    ): string {
        $order = $this->providerOrder();
        $lastError = null;

        foreach ($order as $provider) {
            if (! $this->providerIsReady($provider)) {
                continue;
            }

            try {
                return match ($provider) {
                    'gemini' => $jsonMode
                        ? $this->gemini->generateJson($systemPrompt, $userMessage, $maxOutputTokens, $temperature)
                        : $this->gemini->generate($systemPrompt, $userMessage, $maxOutputTokens, $temperature, false),
                    'claude' => $jsonMode
                        ? $this->claude->generateJson($systemPrompt, $userMessage, $maxOutputTokens, $temperature)
                        : $this->claude->generate($systemPrompt, $userMessage, $maxOutputTokens, $temperature, false),
                    default => throw new RuntimeException("Unknown AI provider: {$provider}"),
                };
            } catch (\Throwable $e) {
                $lastError = $e;
                Log::warning('LLM provider failed, trying next fallback.', [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw $lastError ?? new RuntimeException(
            'AI is not configured. Add AI API keys in backend environment settings and redeploy.'
        );
    }

    public static function isQuotaOrRateLimitError(string $message): bool
    {
        return (bool) preg_match(
            '/quota|rate.?limit|resource exhausted|too many requests|exceeded your current quota|429/i',
            $message
        );
    }

    public static function friendlyQuotaMessage(string $providerError = ''): string
    {
        $wait = '30–60 seconds';
        if (preg_match('/retry in ([\d.]+)s/i', $providerError, $m)) {
            $sec = (int) ceil((float) $m[1]);
            if ($sec > 0 && $sec < 300) {
                $wait = "{$sec} seconds";
            }
        }

        return 'AI service is temporarily busy (request limit reached). '
            ."Wait {$wait} and try again. "
            .'AI Overview and Reports still show real numbers from your database.';
    }

    /**
     * Short error label for admin UI (never expose provider or env var names).
     */
    public static function userFacingErrorCode(string $message): string
    {
        if (self::isQuotaOrRateLimitError($message)) {
            return 'rate_limit';
        }

        return 'request_failed';
    }

    /**
     * Strip provider branding from text that might be shown to users.
     */
    public static function sanitizeForUser(string $message): string
    {
        $text = preg_replace(
            '/\b(gemini|google generative|generativelanguage|anthropic|claude)\b/i',
            'AI',
            $message
        ) ?? $message;
        $text = preg_replace('/\b(GEMINI|ANTHROPIC)_[A-Z0-9_]+\b/', 'AI configuration', $text) ?? $text;
        $text = preg_replace('/gemini[-a-z0-9.]+/i', 'AI model', $text) ?? $text;
        $text = preg_replace('/^(AI\s+)?API\s+error:\s*/i', '', $text) ?? $text;

        $text = trim($text);

        if ($text === '' || self::isQuotaOrRateLimitError($text)) {
            return '';
        }

        if (preg_match('/\b(gemini|GEMINI_|generativelanguage)\b/i', $text)) {
            return '';
        }

        return $text;
    }

    public function generateJson(
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens = 768,
        float $temperature = 0.2
    ): string {
        return $this->generate($systemPrompt, $userMessage, $maxOutputTokens, $temperature, true);
    }

    /**
     * @return list<string>
     */
    private function providerOrder(): array
    {
        $raw = (string) config('ai.providers', 'gemini,claude');
        $parts = array_filter(array_map('trim', explode(',', $raw)));

        return $parts !== [] ? $parts : ['gemini', 'claude'];
    }

    private function providerIsReady(string $provider): bool
    {
        return match ($provider) {
            'gemini' => $this->gemini->isConfigured(),
            'claude' => $this->claude->isConfigured(),
            default => false,
        };
    }
}

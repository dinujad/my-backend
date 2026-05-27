<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeService
{
    public function isConfigured(): bool
    {
        return filled(config('ai.anthropic_api_key'));
    }

    public function generate(
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens = 1024,
        float $temperature = 0.2,
        bool $jsonMode = false
    ): string {
        if (! $this->isConfigured()) {
            throw new RuntimeException('AI service is not configured.');
        }

        $system = $systemPrompt;
        if ($jsonMode) {
            $system .= "\n\nIMPORTANT: Reply with valid JSON only. No markdown fences, no extra text.";
        }

        $response = Http::timeout((int) config('ai.timeout_seconds', 60))
            ->withHeaders([
                'x-api-key' => (string) config('ai.anthropic_api_key'),
                'anthropic-version' => (string) config('ai.anthropic_version', '2023-06-01'),
            ])
            ->acceptJson()
            ->post(rtrim((string) config('ai.anthropic_base_url'), '/').'/v1/messages', [
                'model' => (string) config('ai.claude_model', 'claude-3-5-haiku-20241022'),
                'max_tokens' => $maxOutputTokens,
                'temperature' => $temperature,
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', $response->body());
            if (LlmGatewayService::isQuotaOrRateLimitError($message)) {
                throw new RuntimeException($message);
            }

            throw new RuntimeException('AI service is temporarily unavailable. Please try again.');
        }

        $text = data_get($response->json(), 'content.0.text');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('AI returned an empty response.');
        }

        return trim($text);
    }

    public function generateJson(
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens = 768,
        float $temperature = 0.2
    ): string {
        return $this->generate($systemPrompt, $userMessage, $maxOutputTokens, $temperature, true);
    }
}

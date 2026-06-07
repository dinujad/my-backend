<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * OpenAI-compatible LLM gateway (kodekloud proxy).
 * Tries each model in CLAUDE_MODELS order; on quota/error moves to next.
 * Falls back to native Anthropic format when ANTHROPIC_BASE_URL points to anthropic.com.
 */
class ClaudeService
{
    public function isConfigured(): bool
    {
        return filled(config('ai.anthropic_api_key'));
    }

    /** @return list<string> */
    private function modelList(): array
    {
        $raw = (string) config('ai.claude_models', config('ai.claude_model', 'claude-sonnet-4-5'));
        $models = array_filter(array_map('trim', explode(',', $raw)));
        return $models !== [] ? array_values($models) : ['claude-sonnet-4-5'];
    }

    private function isOpenAiCompatible(): bool
    {
        $base = (string) config('ai.anthropic_base_url', 'https://api.anthropic.com');
        return (string) config('ai.claude_api_format', 'auto') === 'openai'
            || ! str_contains($base, 'anthropic.com');
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

        return $this->generateWithFallback($system, $userMessage, $maxOutputTokens, $temperature);
    }

    /**
     * @param  list<array{role:string,content:string}>  $history  Previous turns (user/assistant).
     */
    public function generateWithHistory(
        string $systemPrompt,
        string $userMessage,
        array $history = [],
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

        return $this->generateWithFallback($system, $userMessage, $maxOutputTokens, $temperature, $history);
    }

    /**
     * @param  list<array{role:string,content:string}>  $history
     */
    private function generateWithFallback(
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens,
        float $temperature,
        array $history = []
    ): string {
        $models  = $this->modelList();
        $lastErr = null;

        foreach ($models as $model) {
            try {
                return $this->isOpenAiCompatible()
                    ? $this->callOpenAiCompat($model, $systemPrompt, $userMessage, $history, $maxOutputTokens, $temperature)
                    : $this->callNativeAnthropic($model, $systemPrompt, $userMessage, $maxOutputTokens, $temperature);
            } catch (\Throwable $e) {
                $lastErr = $e;
                Log::warning('ClaudeService model failed, trying next.', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw $lastErr ?? new RuntimeException('All AI models exhausted without a response.');
    }

    /**
     * @param  list<array{role:string,content:string}>  $history
     */
    private function callOpenAiCompat(
        string $model,
        string $systemPrompt,
        string $userMessage,
        array $history,
        int $maxOutputTokens,
        float $temperature
    ): string {
        $base = rtrim((string) config('ai.anthropic_base_url'), '/');
        $base = preg_replace('#/v1$#', '', $base);

        // Build messages: system → history turns → current user message
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $turn) {
            if (in_array($turn['role'] ?? '', ['user', 'assistant'], true) && filled($turn['content'] ?? '')) {
                $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = Http::timeout((int) config('ai.timeout_seconds', 60))
            ->withHeaders(['Authorization' => 'Bearer ' . (string) config('ai.anthropic_api_key')])
            ->acceptJson()
            ->post("{$base}/v1/chat/completions", [
                'model'       => $model,
                'max_tokens'  => $maxOutputTokens,
                'temperature' => $temperature,
                'messages'    => $messages,
            ]);

        if (! $response->successful()) {
            $msg = (string) data_get($response->json(), 'error.message', $response->body());
            throw new RuntimeException($msg ?: "HTTP {$response->status()} from model {$model}");
        }

        $text = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException("Empty response from model {$model}.");
        }

        return trim($text);
    }

    private function callNativeAnthropic(
        string $model,
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens,
        float $temperature
    ): string {
        $base = rtrim((string) config('ai.anthropic_base_url'), '/');

        $response = Http::timeout((int) config('ai.timeout_seconds', 60))
            ->withHeaders([
                'x-api-key'         => (string) config('ai.anthropic_api_key'),
                'anthropic-version' => (string) config('ai.anthropic_version', '2023-06-01'),
            ])
            ->acceptJson()
            ->post("{$base}/v1/messages", [
                'model'      => $model,
                'max_tokens' => $maxOutputTokens,
                'temperature' => $temperature,
                'system'     => $systemPrompt,
                'messages'   => [['role' => 'user', 'content' => $userMessage]],
            ]);

        if (! $response->successful()) {
            $msg = (string) data_get($response->json(), 'error.message', $response->body());
            throw new RuntimeException($msg ?: "HTTP {$response->status()} from model {$model}");
        }

        $text = data_get($response->json(), 'content.0.text');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException("Empty response from model {$model}.");
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

    public function generateJsonWithHistory(
        string $systemPrompt,
        string $userMessage,
        array $history = [],
        int $maxOutputTokens = 768,
        float $temperature = 0.2
    ): string {
        return $this->generateWithHistory($systemPrompt, $userMessage, $history, $maxOutputTokens, $temperature, true);
    }
}

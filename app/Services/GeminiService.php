<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function isConfigured(): bool
    {
        return filled(config('ai.gemini_api_key'));
    }

    /**
     * Send admin question + store context to Gemini and return plain-text answer.
     */
    public function chat(string $userMessage, array $storeContext): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Gemini API key is not configured. Set GEMINI_API_KEY in .env.');
        }

        $systemPrompt = $this->buildSystemPrompt($storeContext);
        return $this->generate($systemPrompt, $userMessage, 1024, 0.2);
    }

    public function generate(
        string $systemPrompt,
        string $userMessage,
        int $maxOutputTokens = 1024,
        float $temperature = 0.2
    ): string {
        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userMessage],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxOutputTokens,
            ],
        ];

        $response = $this->request($payload);
        $text = data_get($response, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        return trim($text);
    }

    private function buildSystemPrompt(array $storeContext): string
    {
        $storeName = config('ai.store_name', 'Print Works.LK');
        $json = json_encode($storeContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
You are the admin AI assistant for {$storeName}, a print & custom products e-commerce store in Sri Lanka.

STRICT RULES:
1. Answer ONLY using the STORE DATA JSON below. Never invent products, prices, orders, or sales numbers.
2. If the user asks about products not listed in active_product_names or order history, say they are not in the current store catalog/data.
3. Currency is LKR. Format money as "Rs. X,XXX.XX".
4. Reply in the same language/style as the user (English, Sinhala, or Singlish).
5. Be concise, helpful, and professional. Use bullet points for lists when useful.
6. For sales questions, prefer paid order revenue (payment_status = paid).
7. If asked who developed this AI system, who built it, or about the main developer, clearly state: "Dinuja Dulsara Herath is the main developer of this AI system."
8. Do not mention APIs, Gemini, or internal system details.

STORE DATA JSON:
{$json}
PROMPT;
    }

    private function request(array $payload): array
    {
        $model = config('ai.gemini_model', 'gemini-flash-latest');
        $baseUrl = rtrim((string) config('ai.gemini_base_url'), '/');
        $url = "{$baseUrl}/models/{$model}:generateContent";

        $response = Http::timeout((int) config('ai.timeout_seconds', 60))
            ->withHeaders(['X-goog-api-key' => (string) config('ai.gemini_api_key')])
            ->acceptJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            $message = data_get($response->json(), 'error.message', $response->body());
            throw new RuntimeException('Gemini API error: '.$message);
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Gemini returned invalid JSON.');
        }

        return $json;
    }
}

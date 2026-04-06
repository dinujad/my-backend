<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class AiService
{
    /**
     * Base URL for the Python FastAPI service.
     */
    private function baseUrl(): string
    {
        return rtrim(config('ai.base_url'), '/');
    }

    /**
     * Shared request options.
     */
    private function httpClient()
    {
        $timeout = (int) config('ai.timeout_seconds', 10);
        $retries = (int) config('ai.retry_count', 1);

        return Http::timeout($timeout)
            ->retry($retries, 200);
    }

    /**
     * Call Python /api/predict/overview.
     */
    public function getOverview(string $period = 'last_30_days'): array
    {
        $response = $this->getJson(
            $this->baseUrl() . '/api/predict/overview',
            ['period' => $period],
        );

        return $response;
    }

    /**
     * Call Python /api/chat.
     */
    public function chat(string $message, ?int $adminId = null): array
    {
        return $this->postJson(
            $this->baseUrl() . '/api/chat',
            [
                'message' => $message,
                'admin_id' => $adminId,
            ],
        );
    }

    private function getJson(string $url, array $query = []): array
    {
        $response = $this->httpClient()->get($url, $query);
        return $this->handleJsonResponse($response);
    }

    private function postJson(string $url, array $payload): array
    {
        $response = $this->httpClient()->post($url, $payload);
        return $this->handleJsonResponse($response);
    }

    private function handleJsonResponse(Response $response): array
    {
        if (! $response->successful()) {
            throw new \RuntimeException(
                'AI service request failed. Status=' . $response->status() . ' Body=' . $response->body()
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('AI service returned non-JSON response.');
        }

        return $json;
    }
}


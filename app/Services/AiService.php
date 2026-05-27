<?php

namespace App\Services;

use RuntimeException;

class AiService
{
    public function __construct(
        private StoreAiContextService $storeContext,
        private LlmGatewayService $llm,
        private GeminiService $gemini,
    ) {}

    /**
     * Admin overview / predictions — computed from Laravel DB (no external service).
     */
    public function getOverview(string $period = 'last_30_days'): array
    {
        return $this->storeContext->getOverview($period);
    }

    /**
     * Admin chat — Gemini answers using live store data from this Laravel app.
     */
    public function chat(string $message, ?int $adminId = null): array
    {
        $intent = $this->detectIntent($message);
        $context = $this->storeContext->buildCompactContext();

        if (! $this->llm->isConfigured()) {
            return [
                'response_text' => 'AI is not configured yet. Add AI API keys in your backend environment settings, then redeploy. Until then, use AI Overview and Reports for real store numbers.',
                'intent' => $intent,
                'data' => $context['summary'],
                'metrics' => $context['periods']['today'] ?? null,
                'recommendations' => [],
                'table_data' => null,
                'confidence' => 0,
                'error' => 'not_configured',
            ];
        }

        try {
            $systemPrompt = $this->gemini->buildAdminSystemPrompt($context);
            $answer = $this->llm->generate($systemPrompt, $message, 1024, 0.2, false);

            return [
                'response_text' => $answer,
                'intent' => $intent,
                'data' => $context['summary'],
                'metrics' => $this->metricsForIntent($intent, $context),
                'recommendations' => [],
                'table_data' => $this->tableDataForIntent($intent, $context),
                'confidence' => 0.92,
                'error' => null,
            ];
        } catch (RuntimeException $e) {
            if (LlmGatewayService::isQuotaOrRateLimitError($e->getMessage())) {
                return [
                    'response_text' => LlmGatewayService::friendlyQuotaMessage($e->getMessage()),
                    'intent' => $intent,
                    'data' => $context['summary'],
                    'metrics' => $context['periods']['today'] ?? null,
                    'recommendations' => [],
                    'table_data' => null,
                    'confidence' => 0,
                    'error' => 'rate_limit',
                ];
            }

            throw $e;
        }
    }

    private function detectIntent(string $message): string
    {
        $m = mb_strtolower(trim($message));

        if (preg_match('/product|item|stock|inventory|catalog|kohomada|kohomda|kawda/i', $m)) {
            return 'products';
        }
        if (preg_match('/sales|revenue|income|order|ada|month|week|gana|sales/i', $m)) {
            return 'sales';
        }
        if (preg_match('/customer|client|buyer/i', $m)) {
            return 'customers';
        }
        if (preg_match('/category|categories/i', $m)) {
            return 'categories';
        }
        if (preg_match('/forecast|predict|future|next/i', $m)) {
            return 'forecast';
        }

        return 'general';
    }

    private function metricsForIntent(string $intent, array $context): ?array
    {
        return match ($intent) {
            'sales' => $context['periods']['today'] ?? null,
            'products' => [
                'total_products' => $context['summary']['total_products'] ?? 0,
                'active_products' => $context['summary']['active_products'] ?? 0,
            ],
            'customers' => [
                'customers' => $context['summary']['customers'] ?? 0,
            ],
            default => $context['summary'] ?? null,
        };
    }

    private function tableDataForIntent(string $intent, array $context): ?array
    {
        if ($intent === 'products') {
            return collect($context['low_stock_products'] ?? [])
                ->map(fn ($row) => [
                    'Product' => $row['name'] ?? '',
                    'SKU' => $row['sku'] ?? '',
                    'Stock' => $row['stock'] ?? '',
                ])
                ->take(8)
                ->values()
                ->all() ?: null;
        }

        if ($intent === 'sales') {
            return collect($context['top_products_last_30_days'] ?? [])
                ->map(fn ($row) => [
                    'Product' => $row['product_name'] ?? '',
                    'Qty sold' => $row['quantity'] ?? '',
                    'Revenue (LKR)' => $row['revenue_lkr'] ?? '',
                ])
                ->take(8)
                ->values()
                ->all() ?: null;
        }

        return null;
    }
}

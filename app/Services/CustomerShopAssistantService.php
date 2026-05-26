<?php

namespace App\Services;

use App\Models\Product;
use RuntimeException;

class CustomerShopAssistantService
{
    public function __construct(
        private GeminiService $gemini,
    ) {}

    public function ask(string $message): array
    {
        if (! $this->gemini->isConfigured()) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $products = Product::query()
            ->active()
            ->with('category:id,name')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(120)
            ->get(['id', 'name', 'slug', 'category_id', 'short_description', 'description', 'material', 'price']);

        $catalog = $products->map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'category' => $p->category?->name,
                'material' => $p->material,
                'price_lkr' => $p->price !== null ? (float) $p->price : null,
                'summary' => trim((string) ($p->short_description ?: $p->description ?: '')),
            ];
        })->values()->all();

        $storeName = config('ai.store_name', 'Print Works.LK');
        $catalogJson = json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $systemPrompt = <<<PROMPT
You are a customer-facing shopping assistant for {$storeName}.

GOAL:
- Help customers choose suitable products based on their needs (event type, quantity, budget, material, usage).
- Recommend only products from the provided catalog JSON.

STRICT RULES:
1. Never reveal or discuss internal business data (sales, revenue, order counts, costs, margins, staff details).
2. Do not mention products outside the given catalog.
3. If no strong match exists, ask 1-2 short follow-up questions.
4. Reply in the customer's language style (English/Sinhala/Singlish).
5. Keep it practical and concise.
6. Output valid JSON only with this shape:
{
  "reply": "short helpful response for the customer",
  "suggestions": [
    { "name": "Product Name", "slug": "product-slug", "reason": "why this fits" }
  ]
}
7. Maximum 4 suggestions.

CATALOG JSON:
{$catalogJson}
PROMPT;

        $raw = $this->gemini->generate($systemPrompt, $message, 900, 0.25);
        $jsonText = trim($raw);
        if (str_starts_with($jsonText, '```')) {
            $jsonText = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $jsonText) ?? $jsonText;
            $jsonText = trim($jsonText);
        }

        $parsed = json_decode($jsonText, true);

        if (! is_array($parsed)) {
            return [
                'reply' => trim($raw),
                'suggestions' => [],
            ];
        }

        $suggestions = collect($parsed['suggestions'] ?? [])
            ->filter(fn ($row) => is_array($row) && ! empty($row['slug']) && ! empty($row['name']))
            ->map(fn ($row) => [
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'reason' => isset($row['reason']) ? (string) $row['reason'] : '',
            ])
            ->take(4)
            ->values()
            ->all();

        return [
            'reply' => (string) ($parsed['reply'] ?? 'I can help you choose the best product. Tell me your requirement.'),
            'suggestions' => $suggestions,
        ];
    }
}


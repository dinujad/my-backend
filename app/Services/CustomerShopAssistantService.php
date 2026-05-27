<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerShopAssistantService
{
    /** @var list<string> */
    private array $stopwords = [
        'kiyanne', 'kiynne', 'mkkda', 'mokkda', 'mokakda', 'monawada', 'monada', 'what', 'is', 'are',
        'the', 'a', 'an', 'me', 'mage', 'mata', 'one', 'mek', 'meka', 'da', 'd', 'please', 'help',
    ];

    public function __construct(
        private LlmGatewayService $llm,
    ) {}

    public function ask(string $message): array
    {
        $catalog = $this->buildCatalog();

        // Save Gemini free-tier quota: answer simple product lookups locally when possible.
        $terms = $this->extractSearchTerms($message);
        if ($terms !== []) {
            $local = $this->localSearchReply($message, $catalog);
            if ($local['suggestions'] !== []) {
                return $local;
            }
        }

        if ($this->llm->isConfigured()) {
            try {
                return $this->askLlm($message, $catalog);
            } catch (\Throwable $e) {
                Log::warning('Customer AI assistant LLM failed, using local fallback.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->localSearchReply($message, $catalog);
    }

    /**
     * @return list<array{id:int,name:string,slug:string,category:?string,material:?string,price_lkr:?float,summary:string}>
     */
    private function buildCatalog(): array
    {
        return Product::query()
            ->active()
            ->with('category:id,name')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(80)
            ->get(['id', 'name', 'slug', 'category_id', 'short_description', 'description', 'material', 'price'])
            ->map(function (Product $p) {
                $summary = trim((string) ($p->short_description ?: $p->description ?: ''));

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'category' => $p->category?->name,
                    'material' => $p->material,
                    'price_lkr' => $p->price !== null ? (float) $p->price : null,
                    'summary' => Str::limit($summary, 100, '…'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string,mixed>>  $catalog
     * @return array{reply:string,suggestions:list<array{name:string,slug:string,reason:string}>}
     */
    private function askLlm(string $message, array $catalog): array
    {
        $storeName = config('ai.store_name', 'Print Works.LK');
        $catalogJson = json_encode($catalog, JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
You are a customer-facing shopping assistant for {$storeName}.

GOAL:
- Help customers choose suitable products and explain products when asked.
- Recommend only products from the provided catalog JSON.

STRICT RULES:
1. Never reveal internal business data (sales, revenue, order counts, costs, margins).
2. Do not mention products outside the catalog.
3. Reply in the customer's language (English, Sinhala, or Singlish).
4. Keep answers short and helpful.
5. Return JSON only in this exact shape:
{"reply":"...","suggestions":[{"name":"...","slug":"...","reason":"..."}]}
6. Maximum 4 suggestions. Use empty suggestions array if only explaining.
7. Never mention Gemini, Google, Claude, APIs, or internal system details.

CATALOG JSON:
{$catalogJson}
PROMPT;

        $raw = $this->llm->generateJson($systemPrompt, $message, 768, 0.2);
        $parsed = $this->parseAssistantJson($raw);

        if ($parsed !== null) {
            return $parsed;
        }

        // Parsed failed — try local search with whatever text we got.
        $cleanText = $this->cleanRawText($raw);
        $local = $this->localSearchReply($message, $catalog);
        if ($cleanText !== '') {
            $local['reply'] = $cleanText;
        }

        return $local;
    }

    /**
     * @param  list<array<string,mixed>>  $catalog
     * @return array{reply:string,suggestions:list<array{name:string,slug:string,reason:string}>}
     */
    private function localSearchReply(string $message, array $catalog): array
    {
        $terms = $this->extractSearchTerms($message);
        $matches = $this->matchCatalog($catalog, $terms);

        if ($matches === []) {
            return [
                'reply' => 'Mata hari match ekak hambuna na. Budget, quantity, material, use case kiyala podi detail ekak denna. Nethi nam Live Chat ekata yanna puluwan.',
                'suggestions' => [],
            ];
        }

        $suggestions = collect($matches)
            ->take(4)
            ->map(fn (array $p) => [
                'name' => (string) $p['name'],
                'slug' => (string) $p['slug'],
                'reason' => $this->buildReason($p),
            ])
            ->values()
            ->all();

        $names = collect($suggestions)->pluck('name')->implode(', ');

        return [
            'reply' => count($suggestions) === 1
                ? "Me product eka hari match ekak: {$names}. Details balanna product link eka click karanna."
                : "Me requirements walata hari match wena products: {$names}.",
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @return list<string>
     */
    private function extractSearchTerms(string $message): array
    {
        $lower = mb_strtolower(trim($message));
        $parts = preg_split('/[\s,?.!]+/u', $lower) ?: [];
        $terms = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || mb_strlen($part) < 3) {
                continue;
            }
            if (in_array($part, $this->stopwords, true)) {
                continue;
            }
            $terms[] = $part;
        }

        return array_values(array_unique($terms));
    }

    /**
     * @param  list<array<string,mixed>>  $catalog
     * @param  list<string>  $terms
     * @return list<array<string,mixed>>
     */
    private function matchCatalog(array $catalog, array $terms): array
    {
        if ($terms === []) {
            return [];
        }

        $scored = [];
        foreach ($catalog as $product) {
            $haystack = mb_strtolower(implode(' ', array_filter([
                (string) ($product['name'] ?? ''),
                (string) ($product['category'] ?? ''),
                (string) ($product['material'] ?? ''),
                (string) ($product['summary'] ?? ''),
            ])));

            $score = 0;
            foreach ($terms as $term) {
                if (str_contains($haystack, $term)) {
                    $score += mb_strlen($term) >= 5 ? 3 : 2;
                }
            }

            if ($score > 0) {
                $scored[] = ['score' => $score, 'product' => $product];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_map(fn ($row) => $row['product'], array_slice($scored, 0, 6));
    }

    /**
     * @param  array<string,mixed>  $product
     */
    private function buildReason(array $product): string
    {
        $bits = array_filter([
            $product['category'] ? 'Category: '.$product['category'] : null,
            $product['material'] ? 'Material: '.$product['material'] : null,
            $product['price_lkr'] ? 'From Rs. '.number_format((float) $product['price_lkr'], 2) : null,
        ]);

        return $bits !== [] ? implode(' · ', $bits) : 'Matches your search';
    }

    /**
     * @return array{reply:string,suggestions:list<array{name:string,slug:string,reason:string}>}|null
     */
    private function parseAssistantJson(string $raw): ?array
    {
        $jsonText = trim($raw);

        if (str_starts_with($jsonText, '```')) {
            $jsonText = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', $jsonText) ?? $jsonText;
            $jsonText = trim($jsonText);
        }

        if (preg_match('/^json\s*/i', $jsonText)) {
            $jsonText = preg_replace('/^json\s*/i', '', $jsonText) ?? $jsonText;
            $jsonText = trim($jsonText);
        }

        $start = strpos($jsonText, '{');
        $end = strrpos($jsonText, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonText = substr($jsonText, $start, $end - $start + 1);
        }

        $parsed = json_decode($jsonText, true);
        if (! is_array($parsed)) {
            return null;
        }

        $reply = trim((string) ($parsed['reply'] ?? ''));
        if ($reply === '') {
            return null;
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
            'reply' => $reply,
            'suggestions' => $suggestions,
        ];
    }

    private function cleanRawText(string $raw): string
    {
        $text = trim($raw);
        if (str_starts_with($text, '{') || str_contains($text, '"reply"')) {
            return '';
        }

        return preg_replace('/^json\s*/i', '', $text) ?? $text;
    }
}

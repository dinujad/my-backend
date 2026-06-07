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

    /**
     * @param  list<array{role:string,content:string}>  $history  Last N turns from the chat UI.
     */
    public function ask(string $message, array $history = []): array
    {
        $catalog = $this->buildCatalog();

        if ($this->llm->isConfigured()) {
            try {
                return $this->askLlm($message, $catalog, $history);
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
    /**
     * @param  list<array{role:string,content:string}>  $history
     */
    private function askLlm(string $message, array $catalog, array $history = []): array
    {
        $storeName = config('ai.store_name', 'Print Works.LK');
        $catalogJson = json_encode($catalog, JSON_UNESCAPED_UNICODE);

        // Keep last 6 turns (3 user + 3 assistant) to stay within token budget
        $recentHistory = array_slice($history, -6);

        $storePhone  = config('ai.store_phone',   '070 666 8885');
        $storeEmail  = config('ai.store_email',   'sales@printworks.lk');
        $storeUrl    = config('ai.store_url',     'https://printworks.lk');

        $systemPrompt = <<<PROMPT
You are "Printo" 🤖 — the friendly, energetic AI assistant for {$storeName}, a premium custom printing shop in Sri Lanka.

YOUR PERSONALITY:
- Warm, enthusiastic, and conversational — like a knowledgeable friend at a physical store.
- Use relevant emojis naturally (not excessively) to make replies feel lively and easy to read.
- Use the customer's language: English, Sinhala, or Singlish — switch fluidly mid-conversation.
- Be concise but complete. Break long answers into short lines or bullet points.
- Remember previous turns and refer back naturally ("oya kalin kiwa wage..." / "as you mentioned...").
- If a question is unclear, ask one friendly follow-up question to clarify.
- Encourage action: guide customers to the next step (add to cart, request quote, WhatsApp us, etc.).

WHAT YOU CAN ANSWER — EVERYTHING ABOUT THE STORE:

🛒 HOW TO ORDER:
- Visit {$storeUrl} → pick a product → choose size/material/quantity → "Add to Cart" or "Buy Now" → checkout → done! 🎉
- Orders 24/7 online. Need help? WhatsApp us: {$storePhone}

💬 QUOTATION:
- Any product page → red "Request Quote" button → fill details → submit.
- Or go to {$storeUrl}/quote for a custom quote.
- Or WhatsApp {$storePhone} directly with quantity, size, and design details.
- Our team replies within 1 business day with price + timeline. ⚡

🔑 LOGIN & REGISTER:
- Top navigation → "Login" or "Register" → fill your name, email, password → done!
- Once logged in: Dashboard → My Orders, wishlist, and profile. 🙌
- Forgot password? Login page → "Forgot Password" → reset via email.

📦 ORDER STATUS:
- Login → Dashboard → My Orders → see live status: Pending → Processing → Shipped/Completed.
- Want faster updates? WhatsApp your order number to {$storePhone} 📲

🚚 DELIVERY:
- Island-wide delivery across Sri Lanka 🇱🇰
- Delivery: 2–5 business days after production.
- Production: 1–7 business days depending on product and quantity.
- Delivery charges shown at checkout based on location and size.
- Urgent deadline? WhatsApp us BEFORE ordering: {$storePhone}

💳 PAYMENT:
- Visa/Mastercard online payment at checkout ✅
- Bank transfer also available (contact us for details).
- ❌ No cash on delivery — payment needed before production starts.

📞 CONTACT:
- WhatsApp / Phone: {$storePhone}
- Email: {$storeEmail}
- Website: {$storeUrl}
- Physical shop: Biyagama, Sri Lanka 🏪

🎨 DESIGN & CUSTOMISATION:
- Send your artwork via WhatsApp or email after placing/quoting.
- Formats accepted: PDF, AI, PSD, PNG (high res 300dpi+).
- No design? Our in-house design team can help — ask for design service when ordering.

STRICT RULES:
1. NEVER reveal sales figures, revenue, order counts, margins, or any internal business data.
2. Return JSON ONLY in this exact shape — no extra text outside JSON:
{"reply":"...","suggestions":[{"name":"...","slug":"...","reason":"..."}]}
3. "reply" must be a friendly, emoji-enriched, helpful answer.
4. "suggestions" — product recommendations only (max 4). Empty array [] for non-product questions.
5. Never mention Gemini, Google, Claude, OpenAI, AI models, or any internal system details.
6. If someone asks something totally unrelated to the store (weather, news, etc.), politely redirect: "Mata {$storeName} gana vitharai dannne 😄 eeta help karanna puluwanda?"

CATALOG JSON (use for product recommendations):
{$catalogJson}
PROMPT;

        $raw = $this->llm->generateJsonWithHistory($systemPrompt, $message, $recentHistory, 900, 0.2);
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

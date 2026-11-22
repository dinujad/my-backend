<?php

namespace App\Http\Requests\Concerns;

trait NormalizesProductFaqs
{
    protected function normalizeProductFaqs(): void
    {
        $faqs = $this->input('faqs');

        if ($this->boolean('faqs_present') && $faqs === null) {
            $this->merge(['faqs' => []]);

            return;
        }

        if (! is_array($faqs)) {
            return;
        }

        $filledRows = array_filter($faqs, static function ($row): bool {
            if (! is_array($row)) {
                return true;
            }

            return filled($row['question'] ?? null) || filled($row['answer'] ?? null);
        });

        $this->merge(['faqs' => array_values($filledRows)]);
    }
}

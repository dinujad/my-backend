<?php

namespace App\Http\Requests\Concerns;

trait NormalizesProductVariations
{
    /**
     * Remove invalid variation ids (e.g. legacy "new_*" placeholders) before validation.
     * Strip variation price_tiers when tier pricing is off, and drop incomplete tier rows so
     * empty inputs hidden with CSS are not validated as present arrays.
     *
     * @param  array<int, mixed>  $variations
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeVariationsInput(array $variations): array
    {
        foreach ($variations as $i => $v) {
            if (! is_array($v)) {
                unset($variations[$i]);

                continue;
            }

            $rawId = $v['id'] ?? null;
            if ($rawId === '' || $rawId === null) {
                unset($variations[$i]['id']);
            } elseif (is_numeric($rawId) && (int) $rawId > 0) {
                $variations[$i]['id'] = (int) $rawId;
            } else {
                unset($variations[$i]['id']);
            }

            $enabled = filter_var($v['enable_tier_pricing'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $tiers = $v['price_tiers'] ?? null;

            if (! $enabled || ! is_array($tiers)) {
                unset($variations[$i]['price_tiers']);

                continue;
            }

            $filtered = [];
            foreach ($tiers as $t) {
                if (! is_array($t)) {
                    continue;
                }
                $min = $t['min_qty'] ?? null;
                $unit = $t['unit_price'] ?? null;
                if ($min === '' || $min === null || $unit === '' || $unit === null) {
                    continue;
                }
                $filtered[] = $t;
            }

            if ($filtered === []) {
                unset($variations[$i]['price_tiers']);
            } else {
                $variations[$i]['price_tiers'] = array_values($filtered);
            }
        }

        return array_values($variations);
    }
}

<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Listing/card price for simple vs variable products.
 */
final class ProductListingPrice
{
    /**
     * @return array{price: string, numericPrice: float, priceRange: bool, priceMin: float, priceMax: float}
     */
    public static function resolve(Product $product): array
    {
        $fmt = fn (float $v): string => 'Rs. '.number_format($v, 2);
        $base = (float) $product->price;
        $variationPrices = self::variationEffectivePrices($product);

        $useVariations = $variationPrices->isNotEmpty()
            && ($base <= 0 || $product->product_type === 'variable');

        if ($useVariations) {
            $min = (float) $variationPrices->min();
            $max = (float) $variationPrices->max();

            if ($min > 0) {
                if (abs($min - $max) < 0.001) {
                    return [
                        'price' => $fmt($min),
                        'numericPrice' => $min,
                        'priceRange' => false,
                        'priceMin' => $min,
                        'priceMax' => $max,
                    ];
                }

                return [
                    'price' => $fmt($min).' – '.$fmt($max),
                    'numericPrice' => $min,
                    'priceRange' => true,
                    'priceMin' => $min,
                    'priceMax' => $max,
                ];
            }
        }

        return [
            'price' => $fmt($base),
            'numericPrice' => $base,
            'priceRange' => false,
            'priceMin' => $base,
            'priceMax' => $base,
        ];
    }

    /**
     * @return Collection<int, float>
     */
    private static function variationEffectivePrices(Product $product): Collection
    {
        if (! $product->relationLoaded('variations') || $product->variations->isEmpty()) {
            return collect();
        }

        return $product->variations
            ->filter(fn ($v) => $v->is_active !== false)
            ->map(function ($v) {
                $sale = $v->sale_price !== null ? (float) $v->sale_price : 0.0;
                $regular = (float) $v->price;

                return $sale > 0 ? $sale : $regular;
            })
            ->filter(fn (float $p) => $p > 0)
            ->values();
    }
}

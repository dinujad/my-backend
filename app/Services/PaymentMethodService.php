<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PaymentMethodService
{
    /**
     * Get allowed payment methods for a cart of items.
     * Returns intersection of each product's allowed methods.
     * If a product has no restrictions, all active methods are allowed for it.
     */
    public function getAllowedForCart(array $items): Collection
    {
        $activeMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($activeMethods->isEmpty()) {
            return collect();
        }

        $allowedIds = null; // null = unrestricted

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            if (! $productId) continue;

            $product = Product::with('paymentMethods')->find($productId);
            if (! $product) continue;

            $productMethodIds = $product->paymentMethods->pluck('id');

            if ($productMethodIds->isEmpty()) {
                // No restriction — all active methods allowed for this product
                continue;
            }

            // Restrict: take intersection
            $allowedIds = $allowedIds === null
                ? $productMethodIds->toArray()
                : array_intersect($allowedIds, $productMethodIds->toArray());
        }

        if ($allowedIds === null) {
            return $activeMethods;
        }

        return $activeMethods->whereIn('id', array_values($allowedIds))->values();
    }

    /**
     * Get all active payment methods (for checkout display, no cart context).
     */
    public function getActive(): Collection
    {
        return Cache::remember('active_payment_methods', 300, fn () =>
            PaymentMethod::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public function clearCache(): void
    {
        Cache::forget('active_payment_methods');
    }
}

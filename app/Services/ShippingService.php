<?php

namespace App\Services;

use App\Models\ShippingDistrict;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Support\Facades\Cache;

class ShippingService
{
    /**
     * Get available shipping methods with prices for a given district name.
     * Returns an array ready for JSON API response.
     */
    public function getRatesForDistrict(string $districtName, float $orderTotal = 0): array
    {
        $district = ShippingDistrict::with(['zone.activeMethods.rates' => function ($q) use ($districtName) {
            $q->whereHas('district', fn ($d) => $d->where('name', $districtName));
        }])->where('name', $districtName)->first();

        if (! $district || ! $district->zone) {
            return [];
        }

        $results = [];

        foreach ($district->zone->activeMethods as $method) {
            $price = $method->getPriceForDistrict($district->id, $orderTotal);
            $isFreeForOrder = $price === 0.0;

            $results[] = [
                'id'             => $method->id,
                'name'           => $method->name,
                'description'    => $method->description,
                'price'          => $price,
                'is_free'        => $isFreeForOrder,
                'estimated_days' => $method->estimated_days,
                'zone_name'      => $district->zone->name,
            ];
        }

        return $results;
    }

    /**
     * Get cached list of all districts grouped by province.
     */
    public function getAllDistricts(): array
    {
        return Cache::remember('shipping_districts_list', 3600, function () {
            return ShippingDistrict::orderBy('province')->orderBy('name')
                ->get(['id', 'name', 'province'])
                ->groupBy('province')
                ->map(fn ($g) => $g->values())
                ->toArray();
        });
    }

    /**
     * Flat list of district names for dropdown.
     */
    public function getDistrictNames(): array
    {
        return Cache::remember('shipping_district_names', 3600, function () {
            return ShippingDistrict::orderBy('name')->pluck('name')->toArray();
        });
    }

    /**
     * Get cheapest/default shipping price for a district (for order summary preview).
     */
    public function getDefaultPrice(string $districtName, float $orderTotal = 0): ?float
    {
        $rates = $this->getRatesForDistrict($districtName, $orderTotal);
        if (empty($rates)) {
            return null;
        }
        return collect($rates)->min('price');
    }

    /**
     * Resolve method and compute final price (used by CheckoutService).
     */
    public function resolveMethodPrice(int $methodId, string $districtName, float $orderTotal = 0): float
    {
        $district = ShippingDistrict::where('name', $districtName)->first();
        $method = ShippingMethod::with('rates')->find($methodId);

        if (! $method) {
            return 0.0;
        }

        return $method->getPriceForDistrict($district?->id, $orderTotal);
    }

    /**
     * Clear district/rates cache (call after admin updates).
     */
    public function clearCache(): void
    {
        Cache::forget('shipping_districts_list');
        Cache::forget('shipping_district_names');
    }
}

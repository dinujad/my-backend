<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'shipping_zone_id',
        'name',
        'description',
        'base_price',
        'estimated_days',
        'is_active',
        'is_free',
        'free_shipping_threshold',
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'float',
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'free_shipping_threshold' => 'float',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Get effective price for a district. Checks rate override first, then base_price.
     * Respects free shipping rules.
     */
    public function getPriceForDistrict(?int $districtId, float $orderTotal = 0): float
    {
        if ($this->is_free) {
            return 0.0;
        }

        if ($districtId) {
            $rate = $this->rates()->where('shipping_district_id', $districtId)->first();
            if ($rate) {
                if ($rate->is_free) return 0.0;
                if ($rate->free_shipping_threshold && $orderTotal >= $rate->free_shipping_threshold) return 0.0;
                return (float) $rate->price;
            }
        }

        // Fallback to method base price
        if ($this->free_shipping_threshold && $orderTotal >= $this->free_shipping_threshold) {
            return 0.0;
        }

        return (float) $this->base_price;
    }
}

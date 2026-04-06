<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_method_id',
        'shipping_district_id',
        'price',
        'is_free',
        'free_shipping_threshold',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
        'free_shipping_threshold' => 'float',
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(ShippingDistrict::class, 'shipping_district_id');
    }
}

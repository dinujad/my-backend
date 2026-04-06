<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingDistrict extends Model
{
    protected $fillable = [
        'name',
        'province',
        'shipping_zone_id',
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
     * All 25 Sri Lanka districts as a static list for reference.
     */
    public static function allDistricts(): array
    {
        return [
            ['name' => 'Ampara',        'province' => 'Eastern Province'],
            ['name' => 'Anuradhapura', 'province' => 'North Central Province'],
            ['name' => 'Badulla',       'province' => 'Uva Province'],
            ['name' => 'Batticaloa',    'province' => 'Eastern Province'],
            ['name' => 'Colombo',       'province' => 'Western Province'],
            ['name' => 'Galle',         'province' => 'Southern Province'],
            ['name' => 'Gampaha',       'province' => 'Western Province'],
            ['name' => 'Hambantota',    'province' => 'Southern Province'],
            ['name' => 'Jaffna',        'province' => 'Northern Province'],
            ['name' => 'Kalutara',      'province' => 'Western Province'],
            ['name' => 'Kandy',         'province' => 'Central Province'],
            ['name' => 'Kegalle',       'province' => 'Sabaragamuwa Province'],
            ['name' => 'Kilinochchi',   'province' => 'Northern Province'],
            ['name' => 'Kurunegala',    'province' => 'North Western Province'],
            ['name' => 'Mannar',        'province' => 'Northern Province'],
            ['name' => 'Matale',        'province' => 'Central Province'],
            ['name' => 'Matara',        'province' => 'Southern Province'],
            ['name' => 'Monaragala',    'province' => 'Uva Province'],
            ['name' => 'Mullaitivu',    'province' => 'Northern Province'],
            ['name' => 'Nuwara Eliya',  'province' => 'Central Province'],
            ['name' => 'Polonnaruwa',   'province' => 'North Central Province'],
            ['name' => 'Puttalam',      'province' => 'North Western Province'],
            ['name' => 'Ratnapura',     'province' => 'Sabaragamuwa Province'],
            ['name' => 'Trincomalee',   'province' => 'Eastern Province'],
            ['name' => 'Vavuniya',      'province' => 'Northern Province'],
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'product_id',
        'description', 'sku', 'quantity',
        'unit_price', 'discount_percent', 'subtotal',
        'item_notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'       => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'subtotal'         => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

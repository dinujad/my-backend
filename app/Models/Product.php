<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// PaymentMethod relationship added via BelongsToMany above

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_type', 'status', 'visibility',
        'category_id', 'brand_id',
        'name', 'slug', 'description', 'short_description', 'sku',
        'price', 'compare_price', 'cost_price', 'tax_class', 'tax_status',
        'discount_starts_at', 'discount_ends_at',
        'manage_stock', 'stock_quantity', 'low_stock_threshold', 'stock_status',
        'allow_backorders', 'sold_individually', 'min_purchase', 'max_purchase',
        'unit', 'weight', 'length', 'width', 'height', 'shipping_class', 'is_fragile',
        'material',
        'image', 'badge', 'variants_note', 'purchase_note',
        'enable_reviews', 'is_downloadable', 'is_virtual',
        'is_active', 'is_featured', 'sort_order',
        'seo_title', 'seo_description', 'seo_data',
        'specifications', 'highlights', 'faqs', 'attributes_config',
        'page_settings', 'customization_settings'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'manage_stock' => 'boolean',
            'allow_backorders' => 'boolean',
            'sold_individually' => 'boolean',
            'is_fragile' => 'boolean',
            'enable_reviews' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_virtual' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'discount_starts_at' => 'datetime',
            'discount_ends_at' => 'datetime',
            'seo_data' => 'array',
            'specifications' => 'array',
            'highlights' => 'array',
            'faqs' => 'array',
            'attributes_config' => 'array',
            'page_settings' => 'array',
            'customization_settings' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_qty');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function customizationFields(): HasMany
    {
        return $this->hasMany(ProductCustomizationField::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'product_payment_methods');
    }
}

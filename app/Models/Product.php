<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
        'name', 'slug', 'legacy_slugs', 'description', 'short_description', 'sku',
        'price', 'compare_price', 'offer_price', 'cost_price', 'tax_class', 'tax_status',
        'discount_starts_at', 'discount_ends_at',
        'manage_stock', 'stock_quantity', 'low_stock_threshold', 'stock_status',
        'allow_backorders', 'sold_individually', 'min_purchase', 'max_purchase',
        'unit', 'weight', 'length', 'width', 'height', 'shipping_class', 'is_fragile',
        'material',
        'image', 'badge', 'variants_note', 'purchase_note',
        'enable_reviews', 'is_downloadable', 'is_virtual',
        'is_active', 'is_featured', 'is_special_offer', 'is_on_sale', 'is_top_rated', 'sort_order',
        'seo_title', 'seo_description', 'seo_data',
        'specifications', 'highlights', 'faqs', 'attributes_config',
        'page_settings', 'customization_settings'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'offer_price' => 'decimal:2',
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
            'is_special_offer' => 'boolean',
            'is_on_sale' => 'boolean',
            'is_top_rated' => 'boolean',
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
            'legacy_slugs' => 'array',
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

    /**
     * Decode + trim a slug from the URL/API path (do not Str::slug — must match DB for legacy rows).
     */
    public static function normalizeSlugFromRequest(string $slug): string
    {
        $slug = trim(rawurldecode($slug));

        return trim($slug, '/');
    }

    /**
     * DB may still hold legacy values like "foo/" — try a few variants for lookups.
     *
     * @return list<string>
     */
    public static function slugLookupCandidates(string $normalized): array
    {
        $normalized = trim($normalized);
        if ($normalized === '') {
            return [];
        }
        $base = rtrim($normalized, '/');

        return array_values(array_unique(array_filter([
            $normalized,
            $base,
            $base . '/',
        ])));
    }

    /**
     * Single URL segment for /product/{slug}: no slashes, safe for Next + Laravel routing.
     * Does not re-run Str::slug on a non-empty admin slug (avoids changing underscores / casing vs validation).
     */
    public static function slugForStorage(?string $slug, string $name): string
    {
        $base = ($slug !== null && trim((string) $slug) !== '') ? trim((string) $slug) : trim($name);
        $base = rawurldecode($base);
        $base = str_replace(['/', '\\', '?', '#'], '-', $base);
        $base = trim(preg_replace('/\s+/u', '-', $base) ?? '', '-');
        $base = trim(preg_replace('/-+/', '-', $base) ?? '', '-');
        $base = trim($base, '-');

        if ($base !== '') {
            return $base;
        }

        $out = Str::slug($name);
        if ($out === '') {
            $out = 'product-' . substr(bin2hex(random_bytes(4)), 0, 8);
        }

        return $out;
    }

    /**
     * Resolve product by canonical slug or a previous slug kept for SEO (Google index).
     *
     * @param  Builder<Product>  $query  Base query (e.g. ->with(...)->active()).
     */
    public static function firstActiveMatchingSlugOrLegacy(Builder $query, string $rawSlug): ?Product
    {
        $normalized = self::normalizeSlugFromRequest($rawSlug);
        $candidates = self::slugLookupCandidates($normalized);
        if ($candidates === []) {
            return null;
        }

        $bySlug = (clone $query)->whereIn('slug', $candidates)->first();
        if ($bySlug) {
            return $bySlug;
        }

        foreach ($candidates as $c) {
            $byLegacy = (clone $query)
                ->whereNotNull('legacy_slugs')
                ->whereJsonContains('legacy_slugs', $c)
                ->first();
            if ($byLegacy) {
                return $byLegacy;
            }
        }

        return null;
    }

    /**
     * Admin products table: single price or min–max from active variations (uses sale_price when set).
     */
    public function adminListPrice(): string
    {
        $fmt = static fn (float $v): string => 'Rs. ' . number_format($v, 2);

        $vars = $this->relationLoaded('variations')
            ? $this->variations
            : $this->variations()->get();

        $active = $vars->where('is_active', true);

        $effective = $active->map(function (ProductVariation $v) {
            $price = (float) $v->price;
            $sale = $v->sale_price !== null && $v->sale_price !== ''
                ? (float) $v->sale_price
                : null;
            $unit = ($sale !== null && $sale > 0) ? $sale : $price;

            return $unit > 0 ? $unit : null;
        })->filter()->values();

        if ($effective->isNotEmpty()) {
            $min = (float) $effective->min();
            $max = (float) $effective->max();
            if (abs($min - $max) < 0.005) {
                return $fmt($min);
            }

            return $fmt($min) . ' – ' . $fmt($max);
        }

        $base = (float) $this->price;
        if ($base > 0) {
            return $fmt($base);
        }

        return '–';
    }

    /**
     * Admin products table: parent SKU or first variation SKU + count when multiple.
     */
    public function adminListSku(): string
    {
        $sku = trim((string) ($this->sku ?? ''));
        if ($sku !== '') {
            return $sku;
        }

        $vars = $this->relationLoaded('variations')
            ? $this->variations
            : $this->variations()->get();

        $skus = $vars->where('is_active', true)
            ->pluck('sku')
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->unique()
            ->values();

        if ($skus->isEmpty()) {
            return '–';
        }
        if ($skus->count() === 1) {
            return $skus->first();
        }

        return $skus->first() . ' +' . ($skus->count() - 1);
    }

    public function customizationFields(): HasMany
    {
        return $this->hasMany(ProductCustomizationField::class)->orderBy('sort_order');
    }

    public function additionalServices(): HasMany
    {
        return $this->hasMany(ProductAdditionalService::class)->orderBy('sort_order');
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

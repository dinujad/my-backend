<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    /**
     * GET /api/products
     * Returns all active products with their category.
     */
    public function index(): JsonResponse
    {
        $products = Product::with(['category', 'images', 'priceTiers'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => $this->format($p));

        return response()->json($products);
    }

    /**
     * GET /api/products/{slug}
     * Returns a single active product by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'images', 'priceTiers', 'variations.images', 'variations.priceTiers', 'customizationFields'])
            ->withCount([
                'reviews as approved_reviews_count' => fn ($q) => $q->where('is_approved', true),
            ])
            ->withAvg([
                'reviews' => fn ($q) => $q->where('is_approved', true),
            ], 'rating')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($this->format($product));
    }

    /**
     * GET /api/products/by-category/{categorySlug}
     * Returns active products for a given category slug.
     */
    public function byCategory(string $categorySlug): JsonResponse
    {
        $products = Product::with(['category', 'images', 'priceTiers'])
            ->active()
            ->whereHas('category', fn($q) => $q->where('slug', $categorySlug))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => $this->format($p));

        return response()->json($products);
    }

    private function format(Product $p): array
    {
        $price    = (float) $p->price;
        $compare  = $p->compare_price ? (float) $p->compare_price : null;

        // Format LKR price like "Rs. 4,475.00"
        $fmt = fn(float $v) => 'Rs. ' . number_format($v, 2);

        // Absolute URLs via asset() so the browser loads from the API host (works even when
        // Next.js rewrites for /storage are wrong). Requires correct APP_URL in .env and
        // `php artisan storage:link` so public/storage → storage/app/public for uploads.
        $formatImagePath = function (string $path) {
            $path = str_replace('\\', '/', trim($path));
            if ($path === '') {
                return '';
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            $trim = ltrim($path, '/');
            if (str_starts_with($trim, 'images/')) {
                return '/' . $trim;
            }
            if (str_starts_with($trim, 'storage/')) {
                return asset($trim);
            }

            return asset('storage/' . $trim);
        };
        
        $mainImage = '';
        if ($p->image) {
            $mainImage = $formatImagePath($p->image);
        } elseif ($p->images && $p->images->whereNull('product_variation_id')->count() > 0) {
            $mainImage = $formatImagePath($p->images->whereNull('product_variation_id')->sortBy('sort_order')->first()->file_path);
        }

        $avgRating = $p->reviews_avg_rating ?? null;
        $reviewCount = (int) ($p->approved_reviews_count ?? 0);

        return [
            'id'           => $p->id,
            'slug'         => $p->slug,
            'name'         => $p->name,
            'title'        => $p->name,           // alias for frontend compat
            'sku'          => $p->sku ?? 'PW-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
            'description'  => $p->description ?? '',
            'short_description' => $p->short_description ?? '',
            'enable_reviews' => (bool) $p->enable_reviews,
            'manage_stock' => (bool) $p->manage_stock,
            'stock_quantity' => $p->stock_quantity,
            'stock_status' => $p->stock_status ?? 'instock',
            'allow_backorders' => (bool) $p->allow_backorders,
            'review_summary' => [
                'average' => $avgRating !== null ? round((float) $avgRating, 1) : 0,
                'count'   => $reviewCount,
            ],
            'price'        => $fmt($price),
            'numericPrice' => $price,
            'oldPrice'     => $compare ? $fmt($compare) : null,
            'compare_price'=> $compare,
            'image'        => $mainImage,
            'gallery'      => $p->images
                ? $p->images->whereNull('product_variation_id')->sortBy('sort_order')
                    ->filter(fn ($img) => filled($img->file_path))
                    ->map(fn ($img) => $formatImagePath($img->file_path))
                    ->values()
                    ->toArray()
                : [],
            'priceTiers'   => $p->priceTiers ? $p->priceTiers->whereNull('product_variation_id')->map(fn($t) => [
                'min_qty' => $t->min_qty,
                'max_qty' => $t->max_qty,
                'unit_price' => (float) $t->unit_price,
            ])->values()->toArray() : [],
            // Note: never `clone` before relationLoaded() — it returns bool and caused __clone on non-object (500 → Next 404).
            'variations'   => $p->relationLoaded('variations') ? $p->variations->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku ?? '',
                'price' => (float) $v->price,
                'sale_price' => $v->sale_price ? (float) $v->sale_price : null,
                'stock_quantity' => $v->stock_quantity,
                'image' => $v->images && $v->images->count() > 0 ? $formatImagePath($v->images->sortBy('sort_order')->first()->file_path) : null,
                'attributes' => $v->attributes ?? [],
                'priceTiers' => $v->priceTiers ? $v->priceTiers->map(fn($t) => [
                    'min_qty' => $t->min_qty,
                    'max_qty' => $t->max_qty,
                    'unit_price' => (float) $t->unit_price,
                ])->values()->toArray() : [],
            ])->values()->toArray() : [],
            'badge'        => $p->badge,
            'variants_note'=> $p->variants_note,
            'variantsNote' => $p->variants_note,  // alias
            'is_featured'  => $p->is_featured,
            'sort_order'   => $p->sort_order,
            'category'     => $p->category?->name ?? '',
            'categorySlug' => $p->category?->slug ?? '',
            'category_id'  => $p->category_id,
            'material'     => $p->material,
            'seo_title'    => $p->seo_title,
            'seo_description' => $p->seo_description,
            'page_settings' => $p->page_settings,
            'customization_settings' => $p->customization_settings,
            'customization_fields' => $p->relationLoaded('customizationFields') ? $p->customizationFields->map(fn($f) => [
                'id' => $f->id,
                'label' => $f->label,
                'type' => $f->type,
                'is_required' => $f->is_required,
                'options' => is_array($f->options) ? $f->options : (!empty($f->options) ? array_map('trim', explode(',', $f->options)) : null),
                'accepted_extensions' => $f->accepted_extensions
            ])->values()->toArray() : [],
        ];
    }
}

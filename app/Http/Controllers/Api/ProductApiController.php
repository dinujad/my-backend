<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ProductListingPrice;
use App\Support\ProductMediaPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * GET /api/products
     * Returns all active products with their category.
     */
    public function index(): JsonResponse
    {
        $products = Product::with(['category', 'images', 'priceTiers', 'additionalServices', 'variations'])
            ->active()
            ->visibleInCatalog()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => $this->format($p));

        return response()->json($products);
    }

    /**
     * GET /api/products/{slug}
     * Returns a single active product by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $base = Product::with(['category', 'images', 'priceTiers', 'variations.images', 'variations.priceTiers', 'customizationFields', 'additionalServices'])
            ->withCount([
                'reviews as approved_reviews_count' => fn ($q) => $q->where('is_approved', true),
            ])
            ->withAvg([
                'reviews' => fn ($q) => $q->where('is_approved', true),
            ], 'rating')
            ->active();

        $product = Product::firstActiveMatchingSlugOrLegacy($base, $slug);
        if (! $product) {
            abort(404);
        }

        return response()->json($this->format($product));
    }

    /**
     * GET /api/products/by-category/{categorySlug}
     * Returns active products for a given category slug.
     */
    public function byCategory(string $categorySlug): JsonResponse
    {
        $products = Product::with(['category', 'images', 'priceTiers', 'additionalServices', 'variations'])
            ->active()
            ->visibleInCatalog()
            ->whereHas('category', fn($q) => $q->where('slug', $categorySlug))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => $this->format($p));

        return response()->json($products);
    }

    /**
     * GET /api/products/search?q=...&category=slug|all
     * Live suggestions for the storefront header search.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $category = trim((string) $request->input('category', ''));
        $fmt = fn (float $v) => 'Rs. '.number_format($v, 2);

        $query = Product::query()
            ->active()
            ->with(['category', 'variations', 'images'])
            ->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });

        if ($category !== '' && $category !== 'all') {
            $query->whereHas('category', function ($builder) use ($category) {
                $builder->where('slug', $category)->orWhere('name', $category);
            });
        }

        $results = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function (Product $p) {
                $listing = ProductListingPrice::resolve($p);

                $imagePath = $p->image;
                if (! filled($imagePath) && $p->relationLoaded('images')) {
                    $firstGallery = $p->images->whereNull('product_variation_id')->sortBy('sort_order')->first();
                    $imagePath = $firstGallery?->file_path;
                }

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'sku' => $p->sku ?? 'PW-'.str_pad((string) $p->id, 4, '0', STR_PAD_LEFT),
                    'image' => filled($imagePath) ? ProductMediaPath::publicUrl($imagePath) : '',
                    'price' => $listing['price'],
                    'category' => $p->category?->name ?? '',
                ];
            });

        return response()->json($results);
    }

    private function format(Product $p): array
    {
        $listing = ProductListingPrice::resolve($p);
        $price = $listing['numericPrice'];
        $compare = $p->compare_price ? (float) $p->compare_price : null;
        $offerPrice = $p->offer_price ? (float) $p->offer_price : null;

        // Format LKR price like "Rs. 4,475.00"
        $fmt = fn (float $v) => 'Rs. '.number_format($v, 2);

        // Sale-price logic: compare_price is the sale (lower) price the customer pays.
        // When set and lower than regular price, show regular as struck-through "oldPrice"
        // and use the sale price as the displayed "price".
        $oldPrice = null;
        $displayPrice = $price;
        if ($compare && $compare > 0 && $compare < $price) {
            $oldPrice = $fmt($price);
            $displayPrice = $compare;
        }

        // Absolute URLs on API host (APP_URL) — all uploads served from api.printworks.lk/storage/…
        $formatImagePath = fn (string $path) => ProductMediaPath::publicUrl($path);
        
        $mainImage = '';
        $mainImageAlt = null;
        if ($p->image) {
            $mainImage = $formatImagePath($p->image);
            $mainImageAlt = $p->seo_data['main_image_alt'] ?? null;
        } elseif ($p->images && $p->images->whereNull('product_variation_id')->count() > 0) {
            $firstGalleryImage = $p->images->whereNull('product_variation_id')->sortBy('sort_order')->first();
            $mainImage = $formatImagePath($firstGalleryImage->file_path);
            $mainImageAlt = $firstGalleryImage->alt_text;
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
            'min_purchase' => max(1, (int) ($p->min_purchase ?? 1)),
            'max_purchase' => $p->max_purchase !== null ? max(1, (int) $p->max_purchase) : null,
            'review_summary' => [
                'average' => $avgRating !== null ? round((float) $avgRating, 1) : 0,
                'count'   => $reviewCount,
            ],
            'price'        => $displayPrice !== $price ? $fmt($displayPrice) : $listing['price'],
            'numericPrice' => $displayPrice,
            'priceRange'   => $listing['priceRange'],
            'priceMin'     => $listing['priceMin'],
            'priceMax'     => $listing['priceMax'],
            'oldPrice'     => $oldPrice,
            'compare_price'=> $compare,
            'offer_price'  => $offerPrice > 0 ? $offerPrice : null,
            'is_featured'  => (bool) $p->is_featured,
            'is_special_offer' => (bool) $p->is_special_offer,
            'is_on_sale'   => (bool) $p->is_on_sale,
            'is_top_rated' => (bool) $p->is_top_rated,
            'image'        => $mainImage,
            'image_alt'    => $mainImageAlt,
            'gallery'      => $p->images
                ? $p->images->whereNull('product_variation_id')->sortBy('sort_order')
                    ->filter(fn ($img) => filled($img->file_path))
                    ->map(fn ($img) => $formatImagePath($img->file_path))
                    ->values()
                    ->toArray()
                : [],
            'gallery_items' => $p->images
                ? $p->images->whereNull('product_variation_id')->sortBy('sort_order')
                    ->filter(fn ($img) => filled($img->file_path))
                    ->map(fn ($img) => [
                        'src' => $formatImagePath($img->file_path),
                        'alt' => $img->alt_text,
                    ])
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
                'image_alt' => $v->images && $v->images->count() > 0 ? $v->images->sortBy('sort_order')->first()->alt_text : null,
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
            'sort_order'   => $p->sort_order,
            'category'     => $p->category?->name ?? '',
            'categorySlug' => $p->category?->slug ?? '',
            'category_id'  => $p->category_id,
            'material'     => $p->material,
            'seo_title'    => $p->seo_title,
            'seo_description' => $p->seo_description,
            'attributes_config' => $p->attributes_config,
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
            'additional_services' => $p->relationLoaded('additionalServices')
                ? $p->additionalServices
                    ->where('is_active', true)
                    ->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'description' => $s->description,
                        'price' => (float) $s->price,
                        'pricing_type' => $s->pricing_type === 'per_order' ? 'per_order' : 'per_item',
                    ])
                    ->values()
                    ->toArray()
                : [],
        ];
    }
}

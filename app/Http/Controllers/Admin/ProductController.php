<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with(['category', 'brand', 'variations'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands', 'tags'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        unset($data['legacy_slugs']);

        $data['slug'] = Product::slugForStorage($data['slug'] ?? null, $data['name']);
        $data['legacy_slugs'] = [];

        $booleans = [
            'manage_stock', 'allow_backorders', 'sold_individually', 'is_fragile',
            'enable_reviews', 'is_downloadable', 'is_virtual', 'is_active', 'is_featured'
        ];
        foreach ($booleans as $field) {
            $data[$field] = $request->boolean($field);
        }

        $data['seo_data'] = [
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'focus_keyword' => $request->input('focus_keyword'),
            'main_image_alt' => $request->input('main_image_alt'),
        ];
        
        if ($request->filled('attributes_config')) {
            $data['attributes_config'] = json_decode($request->input('attributes_config'), true);
        }

        $data['page_settings'] = $request->input('page_settings', []);
        $data['customization_settings'] = $this->normalizeCustomizationSettings($request->input('customization_settings', []));

        $product = Product::create($data);

        if ($request->has('tags')) {
            $product->tags()->sync($request->input('tags'));
        }

        // Sync allowed payment methods
        $product->paymentMethods()->sync($request->input('payment_method_ids', []));

        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('products', 'public');
            $product->update(['image' => 'storage/' . $path]);
        }

        $this->syncImages($product, $request);
        $this->syncPriceTiers($product, null, $request->boolean('enable_tier_pricing'), $request->input('price_tiers', []));
        $this->syncVariations($product, $request->input('variations', []));
        $this->syncCustomizationFields($product, $request->input('customization_fields', []));
        $this->syncAdditionalServices($product, $request->input('additional_services', []));

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        $product->load(['tags', 'variations.attributeValues', 'images', 'priceTiers', 'variations.images', 'variations.priceTiers', 'customizationFields', 'additionalServices']);

        $customizationFieldsForJs = $product->customizationFields
            ->map(function ($field) {
                $row = $field->toArray();
                $row['options'] = is_array($field->options)
                    ? implode(', ', $field->options)
                    : ($field->options ?? '');

                return $row;
            })
            ->values()
            ->all();

        // Precompute for Blade: @json() breaks on nested single-quoted strings (Blade compiler bug).
        $defaultPageSettings = ['layout_style' => 'default', 'gallery_layout' => 'vertical'];
        $pageSettingsForJs = old('page_settings', $product->page_settings ?? $defaultPageSettings);

        $defaultCustomizationSettings = [
            'enabled' => false,
            'title' => 'Customize this product',
            'flat_fee' => 0,
            'is_required' => false,
            'use_popup' => false,
            'popup_button_label' => '',
        ];
        $rawCustSettings = old('customization_settings', $product->customization_settings ?? []);
        $mergedCustSettings = array_merge($defaultCustomizationSettings, is_array($rawCustSettings) ? $rawCustSettings : []);
        // Cast boolean fields to actual PHP booleans so @json() outputs true/false (not 0/1),
        // ensuring Alpine.js x-model correctly checks/unchecks the toggle on page load.
        $customizationSettingsForJs = array_merge($mergedCustSettings, [
            'enabled'     => (bool) ($mergedCustSettings['enabled'] ?? false),
            'is_required' => (bool) ($mergedCustSettings['is_required'] ?? false),
            'use_popup'   => (bool) ($mergedCustSettings['use_popup'] ?? false),
        ]);

        $variationsForJs = old('variations', $product->variations->map(fn ($v) => [
            'id' => $v->id,
            'sku' => $v->sku,
            'price' => $v->price,
            'sale_price' => $v->sale_price,
            'stock_quantity' => $v->stock_quantity,
            'stock_status' => $v->stock_status,
            'attributes' => $v->attributes ?? [],
            'has_image' => $v->images->count() > 0,
            'image_path' => $v->images->count() > 0 ? $v->images->first()->file_path : null,
            'enable_tier_pricing' => $v->priceTiers->count() > 0,
            'price_tiers' => $v->priceTiers->toArray(),
        ])->toArray());

        // Pre-encode gallery images so @json() in Blade never sees nested single-quoted expressions.
        $existingGalleryImagesForJs = $product->images
            ->whereNull('product_variation_id')
            ->values()
            ->map(fn ($img) => ['id' => $img->id, 'file_path' => $img->file_path, 'alt_text' => $img->alt_text])
            ->toArray();

        $additionalServicesForJs = $product->additionalServices
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description ?? '',
                'price' => (float) $s->price,
                'pricing_type' => $s->pricing_type === 'per_order' ? 'per_order' : 'per_item',
                'is_active' => (bool) $s->is_active,
            ])
            ->values()
            ->all();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'brands',
            'tags',
            'customizationFieldsForJs',
            'pageSettingsForJs',
            'customizationSettingsForJs',
            'variationsForJs',
            'existingGalleryImagesForJs',
            'additionalServicesForJs',
        ));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        unset($data['legacy_slugs']);

        $oldSlug = $product->slug;
        $newSlug = Product::slugForStorage($data['slug'] ?? null, $data['name']);
        $data['slug'] = $newSlug;

        $legacy = $product->legacy_slugs;
        $legacy = is_array($legacy) ? array_values(array_unique(array_filter($legacy))) : [];
        if ($oldSlug !== $newSlug && $oldSlug !== '') {
            if (! in_array($oldSlug, $legacy, true)) {
                $legacy[] = $oldSlug;
            }
        }
        $data['legacy_slugs'] = array_values(array_filter(
            array_unique($legacy),
            fn ($s): bool => is_string($s) && $s !== '' && $s !== $newSlug
        ));

        $booleans = [
            'manage_stock', 'allow_backorders', 'sold_individually', 'is_fragile',
            'enable_reviews', 'is_downloadable', 'is_virtual', 'is_active', 'is_featured'
        ];
        foreach ($booleans as $field) {
            $data[$field] = $request->boolean($field);
        }

        $data['seo_data'] = [
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'focus_keyword' => $request->input('focus_keyword'),
            'main_image_alt' => $request->input('main_image_alt'),
        ];

        if ($request->filled('attributes_config')) {
            $data['attributes_config'] = json_decode($request->input('attributes_config'), true);
        }

        $data['page_settings'] = $request->input('page_settings', []);
        $rawCustomization = $request->input('customization_settings', []);
        $data['customization_settings'] = $this->normalizeCustomizationSettings($rawCustomization);

        \Log::info('ProductController@update customization debug', [
            'product_id'  => $product->id,
            'raw_input'   => $rawCustomization,
            'normalized'  => $data['customization_settings'],
        ]);

        $product->update($data);

        \Log::info('ProductController@update after save', [
            'product_id'             => $product->id,
            'saved_customization'    => $product->fresh()->customization_settings,
        ]);

        if ($request->has('tags')) {
            $product->tags()->sync($request->input('tags'));
        } else {
            $product->tags()->detach();
        }

        if ($request->hasFile('main_image')) {
            if ($product->image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }
            $path = $request->file('main_image')->store('products', 'public');
            $product->update(['image' => 'storage/' . $path]);
        }

        $this->syncImages($product, $request);
        $this->syncPriceTiers($product, null, $request->boolean('enable_tier_pricing'), $request->input('price_tiers', []));
        $this->syncVariations($product, $request->input('variations', []));
        $this->syncCustomizationFields($product, $request->input('customization_fields', []));
        $this->syncAdditionalServices($product, $request->input('additional_services', []));

        // Sync allowed payment methods
        $product->paymentMethods()->sync($request->input('payment_method_ids', []));

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Delete local images safely
        foreach ($product->images as $img) {
            Storage::disk('public')->delete(str_replace('storage/', '', $img->file_path));
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    private function syncImages(Product $product, $request)
    {
        // 1. Keep existing images (handle deletions)
        $existingImageIds = $request->input('existing_images', []);
        $existingImageAlts = $request->input('existing_image_alts', []);
        
        $imagesToDelete = $product->images()
                                  ->whereNull('product_variation_id')
                                  ->whereNotIn('id', $existingImageIds)
                                  ->get();
        
        foreach ($imagesToDelete as $img) {
            Storage::disk('public')->delete(str_replace('storage/', '', $img->file_path));
            $img->delete();
        }

        if (!empty($existingImageIds)) {
            $keptImages = $product->images()
                ->whereNull('product_variation_id')
                ->whereIn('id', $existingImageIds)
                ->get();

            foreach ($keptImages as $img) {
                $img->update([
                    'alt_text' => isset($existingImageAlts[$img->id])
                        ? trim((string) $existingImageAlts[$img->id]) ?: null
                        : null,
                ]);
            }
        }

        // 2. Add new images
        if ($request->hasFile('product_images')) {
            $newImageAlts = $request->input('product_image_alts', []);
            foreach ($request->file('product_images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'file_path' => 'storage/' . $path,
                    'type' => 'gallery',
                    'alt_text' => isset($newImageAlts[$index]) ? (trim((string) $newImageAlts[$index]) ?: null) : null,
                    'sort_order' => 0,
                ]);
            }
        }
    }

    private function syncPriceTiers(Product $product, $variationId, $isTierEnabled, array $tiers)
    {
        $query = $product->priceTiers();
        if ($variationId) {
            $query->where('product_variation_id', $variationId);
        } else {
            $query->whereNull('product_variation_id');
        }

        if (!$isTierEnabled || empty($tiers)) {
            $query->delete();
            return;
        }

        $query->delete();

        foreach ($tiers as $tierData) {
            if (empty($tierData['min_qty']) || empty($tierData['unit_price'])) {
                continue;
            }

            $product->priceTiers()->create([
                'product_variation_id' => $variationId,
                'min_qty' => $tierData['min_qty'],
                'max_qty' => !empty($tierData['max_qty']) ? $tierData['max_qty'] : null,
                'unit_price' => $tierData['unit_price'],
                'label' => $tierData['label'] ?? null,
                'status' => true,
            ]);
        }
    }

    private function syncVariations(Product $product, array $variationsData)
    {
        if ($product->product_type !== 'variable') {
            $variations = $product->variations()->get();
            foreach ($variations as $var) {
                foreach ($var->images as $img) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $img->file_path));
                }
            }
            $product->variations()->delete();
            return;
        }

        $existingIds = [];

        foreach ($variationsData as $varData) {
            $rawId = $varData['id'] ?? null;
            $variationId = is_numeric($rawId) ? (int) $rawId : null;

            $payload = [
                'attributes' => $varData['attributes'] ?? null,
                'sku' => $varData['sku'] ?? null,
                'price' => $varData['price'] ?? 0,
                'sale_price' => $varData['sale_price'] ?? null,
                'stock_quantity' => $varData['stock_quantity'] ?? null,
                'stock_status' => $varData['stock_status'] ?? 'instock',
            ];

            if ($variationId && $product->variations()->whereKey($variationId)->exists()) {
                $variation = $product->variations()->find($variationId);
                $variation->update($payload);
            } else {
                $variation = $product->variations()->create($payload);
            }

            $existingIds[] = $variation->id;

            $varTierEnabled = filter_var($varData['enable_tier_pricing'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $this->syncPriceTiers($product, $variation->id, $varTierEnabled, $varData['price_tiers'] ?? []);
            
            if(isset($varData['image_file']) && $varData['image_file'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $varData['image_file']->store('products/variations', 'public');
                foreach ($variation->images as $img) {
                     Storage::disk('public')->delete(str_replace('storage/', '', $img->file_path));
                     $img->delete();
                }
                $variation->images()->create([
                    'product_id' => $product->id,
                    'file_path' => 'storage/' . $path,
                    'type' => 'variation'
                ]);
            }
        }

        $removedVars = $product->variations()->whereNotIn('id', $existingIds)->get();
        foreach($removedVars as $rv) {
            foreach ($rv->images as $img) {
                Storage::disk('public')->delete(str_replace('storage/', '', $img->file_path));
                $img->delete();
            }
            $rv->delete();
        }
    }

    private function syncCustomizationFields(Product $product, array $fields)
    {
        $product->customizationFields()->delete();
        foreach ($fields as $index => $fieldData) {
            $product->customizationFields()->create([
                'label' => $fieldData['label'],
                'type' => $fieldData['type'],
                'is_required' => filter_var($fieldData['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'options' => !empty($fieldData['options']) ? array_map('trim', explode(',', $fieldData['options'])) : null,
                'accepted_extensions' => $fieldData['accepted_extensions'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function syncAdditionalServices(Product $product, array $services): void
    {
        $product->additionalServices()->delete();
        foreach ($services as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $product->additionalServices()->create([
                'name' => $name,
                'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                'price' => max(0, (float) ($row['price'] ?? 0)),
                'pricing_type' => in_array($row['pricing_type'] ?? '', ['per_order', 'per_item'], true)
                    ? $row['pricing_type']
                    : 'per_item',
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Normalize customization settings values coming from checkbox + hidden inputs.
     * Ensures frontend checks like `enabled == 1 || enabled === true` always work.
     */
    private function normalizeCustomizationSettings(mixed $customizationSettings): array
    {
        if (!is_array($customizationSettings)) {
            return [];
        }

        $normalizeBool = function (mixed $v): int {
            // Hidden + checkbox can sometimes submit as array; pick the last submitted value.
            if (is_array($v)) {
                $v = end($v);
            }

            $b = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($b === null) {
                return ($v === 1 || $v === "1" || $v === true) ? 1 : 0;
            }

            return $b ? 1 : 0;
        };

        return [
            'enabled' => $normalizeBool($customizationSettings['enabled'] ?? false),
            'title' => isset($customizationSettings['title']) ? (string) $customizationSettings['title'] : 'Customize this product',
            'flat_fee' => isset($customizationSettings['flat_fee']) ? (float) $customizationSettings['flat_fee'] : 0,
            'is_required' => $normalizeBool($customizationSettings['is_required'] ?? false),
            'use_popup' => $normalizeBool($customizationSettings['use_popup'] ?? false),
            'popup_button_label' => isset($customizationSettings['popup_button_label']) ? (string) $customizationSettings['popup_button_label'] : '',
        ];
    }
}

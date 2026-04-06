<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_type' => 'required|in:simple,variable,digital,external',
            'status' => 'required|in:draft,published,scheduled,private',
            'visibility' => 'required|in:shop_search,shop_only,search_only,hidden',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'material' => 'nullable|string|max:255',
            
            // Pricing (Required for non-variable)
            'price' => 'nullable|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_class' => 'nullable|string|max:50',
            'tax_status' => 'nullable|string|max:50',
            
            // Inventory
            'manage_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'stock_status' => 'required|in:instock,outofstock,onbackorder,preorder',
            'allow_backorders' => 'boolean',
            'sold_individually' => 'boolean',
            'min_purchase' => 'integer|min:1',
            'max_purchase' => 'nullable|integer|min:1',
            
            // Shipping
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'shipping_class' => 'nullable|string|max:50',
            'is_fragile' => 'boolean',
            
            // Settings
            'purchase_note' => 'nullable|string',
            'enable_reviews' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_virtual' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            
            // Extensibility
            'seo_title' => 'nullable|string|max:60',
            'seo_description' => 'nullable|string|max:160',
            'focus_keyword' => 'nullable|string|max:255',
            
            'attributes_config' => 'nullable|json',
            
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            
            // Local Images
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'existing_images' => 'nullable|array',
            'product_images' => 'nullable|array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Tier Pricing
            'enable_tier_pricing' => 'boolean',
            'price_tiers' => 'nullable|array',
            'price_tiers.*.min_qty' => 'required_with:price_tiers|integer|min:1',
            'price_tiers.*.max_qty' => 'nullable|integer|gt:price_tiers.*.min_qty',
            'price_tiers.*.unit_price' => 'required_with:price_tiers|numeric|min:0',
            'price_tiers.*.label' => 'nullable|string|max:100',

            // Variations (if product_type = variable)
            'variations' => 'nullable|array',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.sale_price' => 'nullable|numeric|min:0',
            'variations.*.stock_quantity' => 'nullable|integer',
            'variations.*.stock_status' => 'required_with:variations|in:instock,outofstock,onbackorder',
            
            'variations.*.image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'variations.*.enable_tier_pricing' => 'boolean',
            'variations.*.price_tiers' => 'nullable|array',
            'variations.*.price_tiers.*.min_qty' => 'required_with:variations.*.price_tiers|integer|min:1',
            'variations.*.price_tiers.*.min_qty' => 'required_with:variations.*.price_tiers|integer|min:1',
            'variations.*.price_tiers.*.max_qty' => 'nullable|integer',
            'variations.*.price_tiers.*.unit_price' => 'required_with:variations.*.price_tiers|numeric|min:0',
            'variations.*.price_tiers.*.label' => 'nullable|string|max:100',

            // Customization & Display Settings
            'page_settings' => 'nullable|array',
            'customization_settings' => 'nullable|array',
            'customization_fields' => 'nullable|array',
            'customization_fields.*.label' => 'required_with:customization_fields|string|max:255',
            'customization_fields.*.type' => 'required_with:customization_fields|string|in:text,textarea,select,radio,file,number',
            'customization_fields.*.is_required' => 'boolean',
            'customization_fields.*.options' => 'nullable|string',
            'customization_fields.*.accepted_extensions' => 'nullable|string|max:255',
        ];
    }
}

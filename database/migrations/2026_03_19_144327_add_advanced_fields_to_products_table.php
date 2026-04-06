<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_type', ['simple', 'variable', 'digital', 'external'])->default('simple')->after('id');
            $table->enum('status', ['draft', 'published', 'scheduled', 'private'])->default('published')->after('is_active');
            $table->enum('visibility', ['shop_search', 'shop_only', 'search_only', 'hidden'])->default('shop_search')->after('status');
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            
            // Pricing
            $table->decimal('cost_price', 12, 2)->nullable()->after('compare_price');
            $table->string('tax_class', 50)->nullable();
            $table->string('tax_status', 50)->default('taxable');
            $table->timestamp('discount_starts_at')->nullable();
            $table->timestamp('discount_ends_at')->nullable();

            // Inventory
            $table->boolean('manage_stock')->default(false);
            $table->integer('stock_quantity')->nullable();
            $table->integer('low_stock_threshold')->nullable();
            $table->enum('stock_status', ['instock', 'outofstock', 'onbackorder', 'preorder'])->default('instock');
            $table->boolean('allow_backorders')->default(false);
            $table->boolean('sold_individually')->default(false);
            $table->integer('min_purchase')->default(1);
            $table->integer('max_purchase')->nullable();

            // Shipping
            $table->string('unit', 50)->nullable(); // piece, kg, box
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->string('shipping_class', 50)->nullable();
            $table->boolean('is_fragile')->default(false);

            // Settings & Digital
            $table->text('purchase_note')->nullable();
            $table->boolean('enable_reviews')->default(true);
            $table->boolean('is_downloadable')->default(false);
            $table->boolean('is_virtual')->default(false);

            // Extensibility
            $table->json('seo_data')->nullable(); // meta_title, meta_description, focus_keyword, canonical
            $table->json('specifications')->nullable();
            $table->json('highlights')->nullable();
            $table->json('faqs')->nullable();
            $table->json('attributes_config')->nullable(); // configuration for variations/attributes per product
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn([
                'product_type', 'status', 'visibility', 'brand_id',
                'cost_price', 'tax_class', 'tax_status', 'discount_starts_at', 'discount_ends_at',
                'manage_stock', 'stock_quantity', 'low_stock_threshold', 'stock_status', 'allow_backorders', 'sold_individually', 'min_purchase', 'max_purchase',
                'unit', 'weight', 'length', 'width', 'height', 'shipping_class', 'is_fragile',
                'purchase_note', 'enable_reviews', 'is_downloadable', 'is_virtual',
                'seo_data', 'specifications', 'highlights', 'faqs', 'attributes_config'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shipping zones (e.g. Western Province, Northern Province)
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Western Province"
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Shipping methods (Standard, Express, etc.)
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // "Standard Delivery"
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0); // fallback price for zone
            $table->string('estimated_days')->nullable();    // "2-3 business days"
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false);      // free shipping toggle
            $table->decimal('free_shipping_threshold', 10, 2)->nullable(); // min order for free
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Districts with zone assignment + per-district price override
        Schema::create('shipping_districts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();                // "Colombo"
            $table->string('province');                      // "Western Province"
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->timestamps();
        });

        // Per-district price overrides per shipping method
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_district_id')->constrained('shipping_districts')->cascadeOnDelete();
            $table->decimal('price', 10, 2);                // Override price for this district
            $table->boolean('is_free')->default(false);      // Free for this specific district
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['shipping_method_id', 'shipping_district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_districts');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};

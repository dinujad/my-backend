<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_promo_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('bold_text')->nullable();
            $table->string('post_text')->nullable();
            $table->string('second_line')->nullable();
            $table->boolean('has_discount')->default(false);
            $table->string('discount_number', 10)->nullable();
            $table->string('action_text')->nullable();
            $table->string('href')->default('/products');
            $table->string('image_alt')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_promo_banners');
    }
};

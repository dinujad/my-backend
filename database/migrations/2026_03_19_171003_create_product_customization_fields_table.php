<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_customization_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('type', 50)->default('text'); // text, select, radio, file
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // For dropdowns/radios
            $table->string('accepted_extensions')->nullable(); // For file uploads .png,.pdf
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_customization_fields');
    }
};

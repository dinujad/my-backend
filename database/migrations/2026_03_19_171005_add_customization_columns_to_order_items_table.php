<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variation_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->json('customizations')->nullable()->after('total_price');
            $table->decimal('customization_fee', 12, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
            $table->dropColumn(['product_variation_id', 'customizations', 'customization_fee']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_special_offer')->default(false)->after('is_featured');
            $table->boolean('is_on_sale')->default(false)->after('is_special_offer');
            $table->boolean('is_top_rated')->default(false)->after('is_on_sale');
            $table->decimal('offer_price', 12, 2)->nullable()->after('compare_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_special_offer', 'is_on_sale', 'is_top_rated', 'offer_price']);
        });
    }
};

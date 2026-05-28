<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_special_offer')) {
                $table->boolean('is_special_offer')->default(false);
            }
            if (! Schema::hasColumn('products', 'is_on_sale')) {
                $table->boolean('is_on_sale')->default(false);
            }
            if (! Schema::hasColumn('products', 'is_top_rated')) {
                $table->boolean('is_top_rated')->default(false);
            }
            if (! Schema::hasColumn('products', 'offer_price')) {
                $table->decimal('offer_price', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $drop = array_filter([
            Schema::hasColumn('products', 'is_special_offer') ? 'is_special_offer' : null,
            Schema::hasColumn('products', 'is_on_sale') ? 'is_on_sale' : null,
            Schema::hasColumn('products', 'is_top_rated') ? 'is_top_rated' : null,
            Schema::hasColumn('products', 'offer_price') ? 'offer_price' : null,
        ]);

        if ($drop !== []) {
            Schema::table('products', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
};

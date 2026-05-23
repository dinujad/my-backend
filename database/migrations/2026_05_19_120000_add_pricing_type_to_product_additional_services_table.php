<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_additional_services', function (Blueprint $table) {
            $table->string('pricing_type', 20)->default('per_item')->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('product_additional_services', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });
    }
};

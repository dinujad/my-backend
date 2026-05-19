<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('additional_services_fee', 12, 2)->default(0)->after('customization_fee');
            $table->json('additional_services')->nullable()->after('customizations');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['additional_services_fee', 'additional_services']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Products created before the admin "Active" checkbox existed were saved with is_active=false
 * even when status=published, so they never appeared on the storefront.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('status', 'published')
            ->where('is_active', false)
            ->update(['is_active' => true]);
    }

    public function down(): void
    {
        // Cannot reliably restore previous inactive flags.
    }
};

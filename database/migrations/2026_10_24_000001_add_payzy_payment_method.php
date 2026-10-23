<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        $existing = DB::table('payment_methods')->where('code', 'payzy')->first();
        $now = now();
        $payzyId = $existing->id ?? DB::table('payment_methods')->insertGetId([
            'code' => 'payzy',
            'name' => 'Payzy — 4 installments',
            'description' => 'Buy now and pay in 4 installments with Payzy.',
            'type' => 'online',
            'is_active' => true,
            'sort_order' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (! Schema::hasTable('product_payment_methods')) {
            return;
        }

        $sourceIds = DB::table('payment_methods')
            ->whereIn('code', ['payhere', 'onepay'])
            ->pluck('id');

        $productIds = DB::table('product_payment_methods')
            ->whereIn('payment_method_id', $sourceIds)
            ->pluck('product_id')
            ->unique();

        foreach ($productIds as $productId) {
            $already = DB::table('product_payment_methods')
                ->where('product_id', $productId)
                ->where('payment_method_id', $payzyId)
                ->exists();
            if (! $already) {
                DB::table('product_payment_methods')->insert([
                    'product_id' => $productId,
                    'payment_method_id' => $payzyId,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }
        $payzyId = DB::table('payment_methods')->where('code', 'payzy')->value('id');
        if (! $payzyId) {
            return;
        }
        if (Schema::hasTable('product_payment_methods')) {
            DB::table('product_payment_methods')->where('payment_method_id', $payzyId)->delete();
        }
        DB::table('payment_methods')->where('id', $payzyId)->delete();
    }
};

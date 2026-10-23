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

        $existing = DB::table('payment_methods')->where('code', 'onepay')->first();
        $now = now();

        if ($existing) {
            $onepayId = $existing->id;
        } else {
            $onepayId = DB::table('payment_methods')->insertGetId([
                'code' => 'onepay',
                'name' => 'Pay Online (OnePay)',
                'description' => 'Card and local wallets on the secure OnePay page. Visa, Mastercard, Amex, FriMi, HelaPay, QPlus, and Google Pay.',
                'type' => 'online',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! Schema::hasTable('product_payment_methods')) {
            return;
        }

        $payhereId = DB::table('payment_methods')->where('code', 'payhere')->value('id');
        if (! $payhereId) {
            return;
        }

        $productIds = DB::table('product_payment_methods')
            ->where('payment_method_id', $payhereId)
            ->pluck('product_id');

        foreach ($productIds as $productId) {
            $already = DB::table('product_payment_methods')
                ->where('product_id', $productId)
                ->where('payment_method_id', $onepayId)
                ->exists();

            if (! $already) {
                DB::table('product_payment_methods')->insert([
                    'product_id' => $productId,
                    'payment_method_id' => $onepayId,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        $onepayId = DB::table('payment_methods')->where('code', 'onepay')->value('id');
        if (! $onepayId) {
            return;
        }

        if (Schema::hasTable('product_payment_methods')) {
            DB::table('product_payment_methods')->where('payment_method_id', $onepayId)->delete();
        }

        DB::table('payment_methods')->where('id', $onepayId)->delete();
    }
};

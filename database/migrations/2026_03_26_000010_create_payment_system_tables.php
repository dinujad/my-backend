<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-configurable payment methods (COD, PayHere, etc.)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // 'cod', 'payhere'
            $table->string('name');                    // 'Cash on Delivery'
            $table->text('description')->nullable();
            $table->string('type')->default('offline'); // 'offline' | 'online'
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Per-product allowed payment methods (pivot)
        Schema::create('product_payment_methods', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->primary(['product_id', 'payment_method_id']);
        });

        // PayHere gateway configuration (single row, keyed settings)
        Schema::create('payhere_settings', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id_live')->nullable();
            $table->text('merchant_secret_live')->nullable();    // stored encrypted
            $table->string('merchant_id_sandbox')->nullable();
            $table->text('merchant_secret_sandbox')->nullable(); // stored encrypted
            $table->string('mode')->default('sandbox');           // 'sandbox' | 'live'
            $table->timestamps();
        });

        // Add payment_method_id & raw_response columns to existing payments table
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'payment_method_id')) {
                $table->foreignId('payment_method_id')
                    ->nullable()
                    ->after('payment_method')
                    ->constrained('payment_methods')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'raw_response')) {
                $table->text('raw_response')->nullable()->after('payment_details');
            }
            if (! Schema::hasColumn('payments', 'gateway_payment_id')) {
                $table->string('gateway_payment_id')->nullable()->after('transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['payment_method_id', 'raw_response', 'gateway_payment_id']);
        });
        Schema::dropIfExists('payhere_settings');
        Schema::dropIfExists('product_payment_methods');
        Schema::dropIfExists('payment_methods');
    }
};

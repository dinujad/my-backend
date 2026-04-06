<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── quote_requests ────────────────────────────────────────────────
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // QR-20260325-0001
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Customer details (stored for guests + logged-in)
            $table->string('customer_name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->text('address')->nullable();

            // Request metadata
            $table->string('status')->default('new');
            // new | reviewing | awaiting_pricing | quoted | sent | customer_responded | approved | rejected | closed

            $table->string('preferred_contact')->default('whatsapp'); // whatsapp | email | phone
            $table->string('preferred_response')->default('whatsapp'); // whatsapp | email
            $table->date('deadline')->nullable();
            $table->string('urgency')->nullable(); // normal | urgent | very_urgent
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        // ── quote_request_items ───────────────────────────────────────────
        Schema::create('quote_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->nullOnDelete();

            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('product_image')->nullable();
            $table->json('variation_attributes')->nullable(); // {"Color":"Red","Size":"A4"}
            $table->unsignedInteger('quantity')->default(1);
            $table->text('item_notes')->nullable();
            $table->timestamps();
        });

        // ── quote_request_status_logs ─────────────────────────────────────
        Schema::create('quote_request_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ── quotations ────────────────────────────────────────────────────
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique(); // QT-20260325-0001
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('draft');
            // draft | ready | sent | viewed | accepted | rejected | expired

            // Customer snapshot (copied at creation time)
            $table->string('customer_name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->text('address')->nullable();

            // Dates
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();

            // Financials
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            // Content
            $table->text('payment_terms')->nullable();
            $table->text('delivery_details')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();

            // PDF
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();

            // Secure public token for customer access
            $table->string('public_token', 64)->unique()->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
        });

        // ── quotation_items ───────────────────────────────────────────────
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description');
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0); // qty * unit_price * (1-discount%)
            $table->text('item_notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── quotation_status_logs ─────────────────────────────────────────
        Schema::create('quotation_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ── quotation_whatsapp_logs ───────────────────────────────────────
        Schema::create('quotation_whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone');
            $table->text('message');
            $table->boolean('success')->default(false);
            $table->text('api_response')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });

        // ── quotation_email_logs ──────────────────────────────────────────
        Schema::create('quotation_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('to_email');
            $table->string('subject');
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });

        // ── quotation_notes ───────────────────────────────────────────────
        Schema::create('quotation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_notes');
        Schema::dropIfExists('quotation_email_logs');
        Schema::dropIfExists('quotation_whatsapp_logs');
        Schema::dropIfExists('quotation_status_logs');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('quote_request_status_logs');
        Schema::dropIfExists('quote_request_items');
        Schema::dropIfExists('quote_requests');
    }
};

<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\QuoteRequestStatusLog;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class QuoteRequestService
{
    public function __construct(private WhatsAppService $whatsapp) {}

    /**
     * Create a new quote request with items.
     *
     * @param array $data  Validated request data
     * @param int|null $customerId  Logged-in customer ID (null for guests)
     */
    public function create(array $data, ?int $customerId = null): QuoteRequest
    {
        return DB::transaction(function () use ($data, $customerId) {
            $request = QuoteRequest::create([
                'request_number'     => $this->generateRequestNumber(),
                'customer_id'        => $customerId,
                'customer_name'      => $data['customer_name'],
                'company_name'       => $data['company_name'] ?? null,
                'email'              => $data['email'],
                'phone'              => $data['phone'],
                'address'            => $data['address'] ?? null,
                'status'             => 'new',
                'preferred_contact'  => $data['preferred_contact'] ?? 'whatsapp',
                'preferred_response' => $data['preferred_response'] ?? 'whatsapp',
                'deadline'           => $data['deadline'] ?? null,
                'urgency'            => $data['urgency'] ?? 'normal',
                'customer_notes'     => $data['notes'] ?? null,
                'last_activity_at'   => now(),
            ]);

            foreach ($data['items'] as $item) {
                $product   = isset($item['product_id']) ? Product::find($item['product_id']) : null;
                $variation = isset($item['product_variation_id']) ? ProductVariation::find($item['product_variation_id']) : null;

                QuoteRequestItem::create([
                    'quote_request_id'      => $request->id,
                    'product_id'            => $product?->id,
                    'product_variation_id'  => $variation?->id,
                    'product_name'          => $item['product_name'] ?? ($product?->name ?? 'Custom Item'),
                    'product_sku'           => $item['product_sku'] ?? ($variation?->sku ?? $product?->sku),
                    'product_image'         => $item['product_image'] ?? ($product?->image),
                    'variation_attributes'  => $variation?->attributes ?? ($item['variation_attributes'] ?? null),
                    'quantity'              => max(1, (int) ($item['quantity'] ?? 1)),
                    'item_notes'            => $item['item_notes'] ?? null,
                ]);
            }

            // Initial status log
            QuoteRequestStatusLog::create([
                'quote_request_id' => $request->id,
                'from_status'      => null,
                'to_status'        => 'new',
                'note'             => 'Quote request submitted.',
            ]);

            $this->notifyAdmins($request);
            $this->sendCustomerConfirmation($request);

            return $request->load('items');
        });
    }

    public function updateStatus(QuoteRequest $request, string $newStatus, ?int $userId = null, ?string $note = null): void
    {
        $old = $request->status;
        $request->update([
            'status'           => $newStatus,
            'last_activity_at' => now(),
        ]);

        QuoteRequestStatusLog::create([
            'quote_request_id' => $request->id,
            'changed_by'       => $userId,
            'from_status'      => $old,
            'to_status'        => $newStatus,
            'note'             => $note,
        ]);
    }

    private function generateRequestNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = QuoteRequest::whereDate('created_at', today())->count() + 1;
        return 'QR-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function notifyAdmins(QuoteRequest $request): void
    {
        // Send WhatsApp notification to admin if configured
        $adminPhone = config('services.whatsapp.admin_notify_phone');
        if ($adminPhone) {
            $msg  = "New Quote Request #{$request->request_number}\n";
            $msg .= "From: {$request->customer_name}\n";
            $msg .= "Phone: {$request->phone}\n";
            $msg .= "Items: " . $request->items->count() . "\n";
            $msg .= "View: " . config('app.frontend_url') . "/admin/quotes/{$request->id}";
            try {
                $this->whatsapp->sendMessage($adminPhone, $msg);
            } catch (\Exception $e) {
                Log::warning("Admin WhatsApp notify failed: " . $e->getMessage());
            }
        }

        // Email notify
        $adminEmails = User::whereIn('role', ['admin', 'super_admin'])
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();

        $notifyEmail = env('CHAT_NOTIFICATION_EMAIL');
        if ($notifyEmail && !in_array($notifyEmail, $adminEmails, true)) {
            $adminEmails[] = $notifyEmail;
        }

        if (!empty($adminEmails)) {
            try {
                Mail::to($adminEmails)->send(new \App\Mail\NewQuoteRequestMail($request));
            } catch (\Exception $e) {
                Log::error("Failed to send new quote request email: " . $e->getMessage());
            }
        }
    }

    private function sendCustomerConfirmation(QuoteRequest $request): void
    {
        if (!empty($request->phone) && $request->preferred_response !== 'email') {
            $msg  = "Hello {$request->customer_name},\n\n";
            $msg .= "Thank you for your quote request!\n";
            $msg .= "Reference: #{$request->request_number}\n\n";
            $msg .= "We've received your request for " . $request->items->count() . " item(s). ";
            $msg .= "Our team will review and get back to you shortly.\n\n";
            $msg .= "Print Works LK";
            try {
                $this->whatsapp->sendMessage($request->phone, $msg);
            } catch (\Exception $e) {
                Log::warning("Customer WhatsApp confirm failed: " . $e->getMessage());
            }
        }
    }
}

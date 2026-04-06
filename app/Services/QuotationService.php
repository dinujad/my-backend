<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationStatusLog;
use App\Models\QuotationWhatsappLog;
use App\Models\QuotationEmailLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotationService
{
    public function __construct(
        private WhatsAppService $whatsapp,
        private QuotationPdfService $pdf
    ) {}

    public function createFromRequest(QuoteRequest $request, array $data, int $userId): Quotation
    {
        return DB::transaction(function () use ($request, $data, $userId) {
            $quotation = Quotation::create([
                'quote_number'      => $this->generateQuoteNumber(),
                'quote_request_id'  => $request->id,
                'created_by'        => $userId,
                'status'            => 'draft',
                'customer_name'     => $request->customer_name,
                'company_name'      => $request->company_name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'address'           => $request->address,
                'quotation_date'    => now()->toDateString(),
                'valid_until'       => $data['valid_until'] ?? now()->addDays(14)->toDateString(),
                'payment_terms'     => $data['payment_terms'] ?? null,
                'delivery_details'  => $data['delivery_details'] ?? null,
                'terms_conditions'  => $data['terms_conditions'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'public_token'      => Str::random(48),
            ]);

            $subtotal = 0;
            foreach (($data['items'] ?? []) as $idx => $item) {
                $qty      = max(1, (int) ($item['quantity'] ?? 1));
                $price    = (float) ($item['unit_price'] ?? 0);
                $disc     = min(100, max(0, (float) ($item['discount_percent'] ?? 0)));
                $lineTotal = $qty * $price * (1 - $disc / 100);
                $subtotal += $lineTotal;

                QuotationItem::create([
                    'quotation_id'     => $quotation->id,
                    'product_id'       => $item['product_id'] ?? null,
                    'description'      => $item['description'],
                    'sku'              => $item['sku'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'subtotal'         => $lineTotal,
                    'item_notes'       => $item['item_notes'] ?? null,
                    'sort_order'       => $idx,
                ]);
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount      = (float) ($data['tax_amount'] ?? 0);
            $total          = $subtotal - $discountAmount + $taxAmount;

            $quotation->update([
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
            ]);

            QuotationStatusLog::create([
                'quotation_id' => $quotation->id,
                'changed_by'   => $userId,
                'from_status'  => null,
                'to_status'    => 'draft',
                'note'         => 'Quotation created.',
            ]);

            return $quotation->load('items');
        });
    }

    public function updateQuotation(Quotation $quotation, array $data, int $userId): Quotation
    {
        return DB::transaction(function () use ($quotation, $data, $userId) {
            // Rebuild items
            $quotation->items()->delete();

            $subtotal = 0;
            foreach (($data['items'] ?? []) as $idx => $item) {
                $qty      = max(1, (int) ($item['quantity'] ?? 1));
                $price    = (float) ($item['unit_price'] ?? 0);
                $disc     = min(100, max(0, (float) ($item['discount_percent'] ?? 0)));
                $lineTotal = $qty * $price * (1 - $disc / 100);
                $subtotal += $lineTotal;

                QuotationItem::create([
                    'quotation_id'     => $quotation->id,
                    'product_id'       => $item['product_id'] ?? null,
                    'description'      => $item['description'],
                    'sku'              => $item['sku'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'subtotal'         => $lineTotal,
                    'item_notes'       => $item['item_notes'] ?? null,
                    'sort_order'       => $idx,
                ]);
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount      = (float) ($data['tax_amount'] ?? 0);
            $total          = $subtotal - $discountAmount + $taxAmount;

            $quotation->update([
                'valid_until'       => $data['valid_until'] ?? $quotation->valid_until,
                'payment_terms'     => $data['payment_terms'] ?? $quotation->payment_terms,
                'delivery_details'  => $data['delivery_details'] ?? $quotation->delivery_details,
                'terms_conditions'  => $data['terms_conditions'] ?? $quotation->terms_conditions,
                'notes'             => $data['notes'] ?? $quotation->notes,
                'subtotal'          => $subtotal,
                'discount_amount'   => $discountAmount,
                'tax_amount'        => $taxAmount,
                'total'             => $total,
                'pdf_path'          => null, // invalidate old PDF
                'pdf_generated_at'  => null,
            ]);

            return $quotation->load('items');
        });
    }

    public function sendViaWhatsApp(Quotation $quotation, int $userId): array
    {
        // Generate PDF first
        $pdfPath = $this->pdf->generate($quotation);

        $frontendUrl = config('app.frontend_url');
        $viewLink    = "{$frontendUrl}/quote/view/{$quotation->public_token}";

        $msg  = "Hello {$quotation->customer_name},\n\n";
        $msg .= "Your quotation #{$quotation->quote_number} from Print Works LK is ready!\n\n";
        $msg .= "Valid until: " . ($quotation->valid_until?->format('d M Y') ?? 'N/A') . "\n";
        $msg .= "Total: Rs. " . number_format($quotation->total, 2) . "\n\n";
        $msg .= "View your quotation here:\n{$viewLink}\n\n";
        $msg .= "Thank you,\nPrint Works LK";

        $success  = $this->whatsapp->sendMessage($quotation->phone, $msg);
        $apiResp  = $success ? 'Message sent successfully.' : 'Message failed to send.';

        QuotationWhatsappLog::create([
            'quotation_id' => $quotation->id,
            'sent_by'      => $userId,
            'phone'        => $quotation->phone,
            'message'      => $msg,
            'success'      => $success,
            'api_response' => $apiResp,
            'sent_at'      => now(),
        ]);

        if ($success) {
            $this->updateStatus($quotation, 'sent', $userId, 'Sent via WhatsApp.');
            $quotation->update(['sent_at' => now()]);
            $quotation->quoteRequest->update([
                'status'           => 'sent',
                'last_activity_at' => now(),
            ]);
        }

        return ['success' => $success, 'message' => $apiResp];
    }

    public function updateStatus(Quotation $quotation, string $newStatus, int $userId, ?string $note = null): void
    {
        $old = $quotation->status;
        $quotation->update(['status' => $newStatus]);

        QuotationStatusLog::create([
            'quotation_id' => $quotation->id,
            'changed_by'   => $userId,
            'from_status'  => $old,
            'to_status'    => $newStatus,
            'note'         => $note,
        ]);
    }

    private function generateQuoteNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = Quotation::whereDate('created_at', today())->count() + 1;
        return 'QT-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Services;

use App\Models\Quotation;
use Illuminate\Support\Facades\Storage;

class QuotationPdfService
{
    /**
     * Generate an HTML-based quotation document.
     * Returns the storage path (relative to storage/app/public).
     *
     * For a proper PDF we would use barryvdh/laravel-dompdf or similar.
     * Here we generate a standalone HTML file served as download,
     * ready to drop in a PDF library when installed.
     */
    public function generate(Quotation $quotation): string
    {
        $quotation->load(['items', 'quoteRequest']);

        $html = $this->buildHtml($quotation);

        $filename = 'quotations/QT-' . $quotation->id . '-' . time() . '.html';
        Storage::disk('public')->put($filename, $html);

        $quotation->update([
            'pdf_path'         => $filename,
            'pdf_generated_at' => now(),
        ]);

        return $filename;
    }

    public function getPublicUrl(Quotation $quotation): ?string
    {
        if (!$quotation->pdf_path) return null;
        return asset('storage/' . $quotation->pdf_path);
    }

    private function buildHtml(Quotation $quotation): string
    {
        $frontendUrl    = config('app.frontend_url', 'https://printworks.lk');
        $date           = $quotation->quotation_date?->format('d M Y') ?? now()->format('d M Y');
        $validUntil     = $quotation->valid_until?->format('d M Y') ?? 'N/A';
        $itemsHtml      = '';

        foreach ($quotation->items as $item) {
            $disc   = $item->discount_percent > 0 ? "<span style='color:#e53e3e;'>-{$item->discount_percent}%</span>" : '-';
            $itemsHtml .= "
            <tr>
                <td style='padding:10px 14px;border-bottom:1px solid #f0f0f0;'>" . e($item->description) . ($item->sku ? "<br><span style='font-size:11px;color:#888;'>SKU: {$item->sku}</span>" : '') . "</td>
                <td style='padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:center;'>{$item->quantity}</td>
                <td style='padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:right;'>Rs. " . number_format($item->unit_price, 2) . "</td>
                <td style='padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:center;'>{$disc}</td>
                <td style='padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:right;font-weight:600;'>Rs. " . number_format($item->subtotal, 2) . "</td>
            </tr>";
            if ($item->item_notes) {
                $itemsHtml .= "<tr><td colspan='5' style='padding:2px 14px 8px;font-size:12px;color:#666;border-bottom:1px solid #f0f0f0;'>Note: " . e($item->item_notes) . "</td></tr>";
            }
        }

        $company    = $quotation->company_name ? "<br>" . e($quotation->company_name) : '';
        $address    = $quotation->address ? "<br>" . e($quotation->address) : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quotation #{$quotation->quote_number}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Segoe UI',Arial,sans-serif; background:#f7f8fa; color:#222; }
  .wrapper { max-width:820px; margin:0 auto; background:#fff; }
  .header { background:#c0392b; color:#fff; padding:36px 40px; display:flex; justify-content:space-between; align-items:center; }
  .header h1 { font-size:28px; font-weight:700; letter-spacing:1px; }
  .header .quote-no { font-size:14px; opacity:.85; margin-top:4px; }
  .badge { background:rgba(255,255,255,.2); border-radius:20px; padding:4px 14px; font-size:13px; font-weight:600; }
  .body { padding:36px 40px; }
  .meta-row { display:flex; gap:40px; margin-bottom:32px; }
  .meta-block h3 { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#888; margin-bottom:8px; }
  .meta-block p { font-size:14px; line-height:1.6; }
  table { width:100%; border-collapse:collapse; margin-bottom:24px; }
  thead tr { background:#f7f8fa; }
  thead th { padding:11px 14px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#666; border-bottom:2px solid #e8e8e8; }
  .totals-row { display:flex; justify-content:flex-end; margin-bottom:32px; }
  .totals-table { width:280px; }
  .totals-table td { padding:7px 0; font-size:14px; }
  .totals-table .total-row td { font-size:17px; font-weight:700; color:#c0392b; border-top:2px solid #e8e8e8; padding-top:10px; }
  .terms { margin-top:32px; padding:20px 24px; background:#f7f8fa; border-radius:8px; font-size:13px; color:#555; line-height:1.7; }
  .terms h4 { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#888; margin-bottom:8px; }
  .footer { padding:24px 40px; border-top:1px solid #eee; display:flex; justify-content:space-between; font-size:12px; color:#999; }
  @media print { body { background:#fff; } }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div>
      <h1>PRINT WORKS LK</h1>
      <div class="quote-no">printworks.lk</div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:22px;font-weight:700;">QUOTATION</div>
      <div class="quote-no">#{$quotation->quote_number}</div>
      <div class="badge" style="margin-top:8px;">{$quotation->status_label}</div>
    </div>
  </div>

  <div class="body">
    <div class="meta-row">
      <div class="meta-block">
        <h3>Prepared For</h3>
        <p><strong>" . e($quotation->customer_name) . "</strong>{$company}{$address}<br>{$quotation->email}<br>{$quotation->phone}</p>
      </div>
      <div class="meta-block">
        <h3>Quotation Details</h3>
        <p>Date: <strong>{$date}</strong><br>Valid Until: <strong>{$validUntil}</strong><br>Quote #: <strong>{$quotation->quote_number}</strong></p>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th style="text-align:left;">Description</th>
          <th style="text-align:center;width:70px;">Qty</th>
          <th style="text-align:right;width:110px;">Unit Price</th>
          <th style="text-align:center;width:80px;">Discount</th>
          <th style="text-align:right;width:120px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>{$itemsHtml}</tbody>
    </table>

    <div class="totals-row">
      <table class="totals-table">
        <tr><td>Subtotal</td><td style="text-align:right;">Rs. " . number_format($quotation->subtotal, 2) . "</td></tr>
        " . ($quotation->discount_amount > 0 ? "<tr><td style='color:#e53e3e;'>Discount</td><td style='text-align:right;color:#e53e3e;'>- Rs. " . number_format($quotation->discount_amount, 2) . "</td></tr>" : '') . "
        " . ($quotation->tax_amount > 0 ? "<tr><td>Tax</td><td style='text-align:right;'>Rs. " . number_format($quotation->tax_amount, 2) . "</td></tr>" : '') . "
        <tr class='total-row'><td><strong>TOTAL</strong></td><td style='text-align:right;'><strong>Rs. " . number_format($quotation->total, 2) . "</strong></td></tr>
      </table>
    </div>

    " . ($quotation->payment_terms ? "<div class='terms'><h4>Payment Terms</h4>" . nl2br(e($quotation->payment_terms)) . "</div>" : '') . "
    " . ($quotation->delivery_details ? "<div class='terms' style='margin-top:12px;'><h4>Delivery Details</h4>" . nl2br(e($quotation->delivery_details)) . "</div>" : '') . "
    " . ($quotation->terms_conditions ? "<div class='terms' style='margin-top:12px;'><h4>Terms & Conditions</h4>" . nl2br(e($quotation->terms_conditions)) . "</div>" : '') . "
    " . ($quotation->notes ? "<div class='terms' style='margin-top:12px;'><h4>Notes</h4>" . nl2br(e($quotation->notes)) . "</div>" : '') . "
  </div>

  <div class="footer">
    <div>Print Works LK | printworks.lk | Thank you for your business!</div>
    <div>Generated: " . now()->format('d M Y H:i') . "</div>
  </div>
</div>
</body>
</html>
HTML;
    }
}

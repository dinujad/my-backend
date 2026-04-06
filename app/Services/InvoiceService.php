<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;

class InvoiceService
{
    public function createInvoice(Order $order): Invoice
    {
        $invoiceNumber = 'INV-' . $order->order_number;
        
        $invoice = Invoice::firstOrCreate(
            ['order_id' => $order->id],
            [
                'invoice_number' => $invoiceNumber,
                'total_amount' => $order->total,
                'paid_amount' => 0,
                'balance_due' => $order->total,
                'status' => 'unpaid',
            ]
        );

        return $invoice;
    }

    public function recordPayment(Invoice $invoice, float $amount): Invoice
    {
        $invoice->paid_amount += $amount;
        $invoice->balance_due = max(0, $invoice->total_amount - $invoice->paid_amount);
        
        if ($invoice->balance_due <= 0) {
            $invoice->status = 'paid';
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partial';
        }
        
        $invoice->save();
        return $invoice;
    }
}

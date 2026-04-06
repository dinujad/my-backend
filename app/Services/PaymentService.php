<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;

class PaymentService
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function processPayment(Order $order, float $amount, string $method, ?string $transactionId = null): Payment
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $order->currency,
            'status' => 'completed',
        ]);

        $order->payment_status = 'paid';
        if ($order->status == 'pending') {
            $order->status = 'processing';
            $order->statusHistory()->create([
                'status' => 'processing',
                'notes' => 'Payment received.',
                'customer_notified' => false,
            ]);
        }
        $order->save();

        $invoice = $order->invoices()->first();
        if (!$invoice) {
            $invoice = $this->invoiceService->createInvoice($order);
        }
        
        $this->invoiceService->recordPayment($invoice, $amount);

        return $payment;
    }
}

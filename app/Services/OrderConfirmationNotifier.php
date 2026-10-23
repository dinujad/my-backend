<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderConfirmationNotifier
{
    public function __construct(
        private EmailService $email,
        private SMSService $sms,
        private WhatsAppService $whatsapp,
    ) {}

    public function send(Order $order): void
    {
        try {
            $this->email->sendOrderEmail($order, 'created');
            $this->email->sendAdminOrderEmail($order);
            $this->sms->sendOrderSms($order, 'created');
            $this->whatsapp->sendOrderConfirmation($order);
        } catch (Throwable $e) {
            Log::error("Failed to send order confirmation for order {$order->id}: ".$e->getMessage());
        }
    }
}

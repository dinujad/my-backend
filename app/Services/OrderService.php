<?php

namespace App\Services;

use App\Models\Order;
use Exception;

class OrderService
{
    protected EmailService $emailService;
    protected SMSService $smsService;

    public function __construct(EmailService $emailService, SMSService $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    public function updateStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        if ($order->status === $newStatus) {
            return $order;
        }

        $order->status = $newStatus;
        $order->save();

        $order->statusHistory()->create([
            'status' => $newStatus,
            'notes' => $notes,
            'customer_notified' => true,
        ]);

        $this->emailService->sendOrderEmail($order, $newStatus);
        
        app(\App\Services\WhatsAppService::class)->sendOrderStatusUpdate($order, $newStatus);

        if ($newStatus === 'shipped') {
            $this->smsService->sendOrderSms($order, 'shipped');
        } elseif ($newStatus === 'completed') {
            $this->smsService->sendOrderSms($order, 'completed');
        }

        return $order;
    }

    public function addShipment(Order $order, array $data): Order
    {
        $order->shipments()->create([
            'shipping_method' => $data['shipping_method'] ?? null,
            'tracking_number' => $data['tracking_number'] ?? null,
            'shipping_notes' => $data['shipping_notes'] ?? null,
            'shipped_at' => now(),
        ]);

        return $this->updateStatus($order, 'shipped', "Order shipped via " . ($data['shipping_method'] ?? 'courier'));
    }
}

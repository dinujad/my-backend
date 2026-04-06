<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl = 'https://wa.hglk.link/api/send_message.php';
    protected string $email;
    protected string $apiKey;

    public function __construct()
    {
        $this->email = config('services.whatsapp.email', 'dulsaradinuja47@gmail.com');
        $this->apiKey = config('services.whatsapp.api_key', 'C0A1C0C348');
    }

    /**
     * Send a WhatsApp message via HostGrap API
     */
    public function sendMessage(string $phone, string $text): bool
    {
        // Format phone: must be in number@c.us format
        $chatId = $this->formatPhone($phone);
        if (!$chatId) {
            return false;
        }

        try {
            $response = Http::post($this->apiUrl, [
                'email' => $this->email,
                'api_key' => $this->apiKey,
                'chatId' => $chatId,
                'text' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success') {
                    return true;
                }
                Log::warning('WhatsApp API returned unsuccessful status: ' . $response->body());
                return false;
            }

            Log::error('WhatsApp API sending failed HTTP error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp API exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Order Confirmation
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        $phone = $this->getOrderPhone($order);
        if (!$phone) {
            return false;
        }

        $customerName = $order->customer ? $order->customer->name : 'Customer';
        if ($order->shipping_address && isset($order->shipping_address['first_name'])) {
            $customerName = trim(($order->shipping_address['first_name'] ?? '') . ' ' . ($order->shipping_address['last_name'] ?? ''));
        }

        $text = "Hello {$customerName},\n\nThank you for your order! Your order #{$order->order_number} has been received successfully.\n\nTotal: Rs. " . number_format($order->total, 2) . "\n\nWe will notify you once it's shipped.\n\nThank you,\nPrint Works LK";

        return $this->sendMessage($phone, $text);
    }

    /**
     * Send Order Shipped Notification
     */
    public function sendOrderShipped(Order $order): bool
    {
        $phone = $this->getOrderPhone($order);
        if (!$phone) {
            return false;
        }

        $customerName = $order->customer ? $order->customer->name : 'Customer';
        if ($order->shipping_address && isset($order->shipping_address['first_name'])) {
            $customerName = trim(($order->shipping_address['first_name'] ?? '') . ' ' . ($order->shipping_address['last_name'] ?? ''));
        }

        $shipment = $order->shipments()->latest()->first();
        $courier = $shipment ? $shipment->shipping_method : 'our delivery partner';
        $tracking = $shipment ? $shipment->tracking_number : 'N/A';

        $text = "Hello {$customerName},\n\nGreat news! Your order #{$order->order_number} has been shipped via {$courier}.\n\n";
        if ($tracking && $tracking !== 'N/A') {
            $text .= "Tracking Number: {$tracking}\n\n";
        }
        $text .= "Thank you for shopping with us!\n\nPrint Works LK";

        return $this->sendMessage($phone, $text);
    }

    /**
     * Send Order Status Update Notification (Processing, Delivered, Cancelled)
     */
    public function sendOrderStatusUpdate(Order $order, string $newStatus): bool
    {
        $phone = $this->getOrderPhone($order);
        if (!$phone) {
            return false;
        }

        $customerName = $order->customer ? $order->customer->name : 'Customer';
        if ($order->shipping_address && isset($order->shipping_address['first_name'])) {
            $customerName = trim(($order->shipping_address['first_name'] ?? '') . ' ' . ($order->shipping_address['last_name'] ?? ''));
        }

        $messages = [
            'pending' => "is now pending.",
            'processing' => "is currently being processed.",
            'delivered' => "has been delivered. Thank you for shopping with us!",
            'cancelled' => "has been cancelled. If you have any questions, please contact us.",
        ];

        $statusMessage = $messages[$newStatus] ?? "status has been updated to {$newStatus}.";

        $text = "Hello {$customerName},\n\nYour order #{$order->order_number} {$statusMessage}\n\nPrint Works LK";

        return $this->sendMessage($phone, $text);
    }

    /**
     * Send Payment Status Update Notification (Paid, Refunded, Unpaid)
     */
    public function sendPaymentStatusUpdate(Order $order, string $newPaymentStatus): bool
    {
        $phone = $this->getOrderPhone($order);
        if (!$phone) {
            return false;
        }

        $customerName = $order->customer ? $order->customer->name : 'Customer';
        if ($order->shipping_address && isset($order->shipping_address['first_name'])) {
            $customerName = trim(($order->shipping_address['first_name'] ?? '') . ' ' . ($order->shipping_address['last_name'] ?? ''));
        }

        $messages = [
            'unpaid' => "is currently marked as unpaid.",
            'paid' => "has been fully paid. Thank you for your payment!",
            'refunded' => "payment has been refunded.",
        ];

        $statusMessage = $messages[$newPaymentStatus] ?? "payment status has been updated to {$newPaymentStatus}.";

        $text = "Hello {$customerName},\n\nThe payment status for your order #{$order->order_number} {$statusMessage}\n\nPrint Works LK";

        return $this->sendMessage($phone, $text);
    }

    /**
     * Get phone number from Order
     */
    private function getOrderPhone(Order $order): ?string
    {
        if ($order->shipping_address && !empty($order->shipping_address['phone'])) {
            return $order->shipping_address['phone'];
        }

        if ($order->customer && !empty($order->customer->phone)) {
            return $order->customer->phone;
        }

        return null;
    }

    /**
     * Send Welcome Notification
     */
    public function sendWelcomeMessage(string $phone, string $name): bool
    {
        $text = "Hello {$name},\n\nWelcome to Print Works LK! Thank you for registering a new account with us.\n\nYou can now easily purchase products and track your orders from the dashboard.\n\nThank you,\nPrint Works LK";

        return $this->sendMessage($phone, $text);
    }

    /**
     * Format phone number to WhatsApp format (e.g. 94725729000@c.us)
     */
    private function formatPhone(string $phone): ?string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 07... to 947... (Sri Lanka standard)
        if (str_starts_with($phone, '07')) {
            $phone = '94' . substr($phone, 1);
        }

        // Check if length is realistic for an international number
        if (strlen($phone) < 10) {
            return null;
        }

        return $phone . '@c.us';
    }
}

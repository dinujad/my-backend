<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSService
{
    private string $apiKey;
    private string $senderId;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('TEXTLK_API_KEY', '2096|ga63KB0Gy8MSNZFfnQLqwN2YV3RTeAfOh4dXMhlJ92c40cf0');
        $this->senderId = env('TEXTLK_SENDER_ID', 'PrintWorks');
        $this->apiUrl = env('TEXTLK_URL', 'https://app.text.lk/api/v3/sms/send');
    }

    /**
     * Send an SMS using Text.lk API
     */
    public function sendMessage(string $phone, string $text): bool
    {
        $phone = $this->formatPhone($phone);
        if (!$phone) {
            return false;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'recipient' => $phone,
                    'sender_id' => $this->senderId,
                    'type' => 'plain',
                    'message' => $text,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success') {
                    return true;
                }
                Log::warning('Text.lk API returned unsuccessful status: ' . $response->body());
                return false;
            }

            Log::error('Text.lk API sending failed HTTP error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Text.lk API exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS based on order event.
     */
    public function sendOrderSms(Order $order, string $eventType): void
    {
        $phoneNumber = $order->customer->phone ?? $order->shipping_address['phone'] ?? null;
        
        if (!$phoneNumber) {
            return;
        }

        $customerName = $order->customer->name ?? $order->shipping_address['first_name'] ?? 'Customer';
        
        $message = $this->generateMessage($order, $customerName, $eventType);
        
        if (!$message) return;

        $isSent = false;
        $deliveryStatus = 'failed';

        try {
            $isSent = $this->sendMessage($phoneNumber, $message);
            $deliveryStatus = $isSent ? 'sent' : 'api_rejected';
        } catch (\Exception $e) {
            Log::error("SMS sending failed for order {$order->id}: {$e->getMessage()}");
            $deliveryStatus = 'error: ' . $e->getMessage();
        }

        SmsLog::create([
            'order_id' => $order->id,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'event_type' => $eventType,
            'is_sent' => $isSent,
            'delivery_status' => $deliveryStatus,
            'sent_at' => $isSent ? now() : null,
        ]);
    }

    private function generateMessage(Order $order, string $customerName, string $eventType): ?string
    {
        switch ($eventType) {
            case 'created':
                return "Hello {$customerName}, your order #{$order->order_number} has been received successfully by PrintWorksLK. We will update you once it is processed.";
            case 'shipped':
                $shipment = $order->shipments()->latest()->first();
                $method = $shipment?->shipping_method ?? 'our delivery partner';
                $tracking = $shipment?->tracking_number ? " Tracking No: {$shipment->tracking_number}." : "";
                return "Hello {$customerName}, your order #{$order->order_number} has been shipped via {$method}.{$tracking} Thank you - PrintWorksLK";
            case 'completed':
                return "Hello {$customerName}, your order #{$order->order_number} has been completed. Thank you for shopping with PrintWorksLK.";
            default:
                return null;
        }
    }

    public function resendSms(int $smsLogId): void
    {
        $log = SmsLog::findOrFail($smsLogId);
        $order = $log->order;
        
        if ($order) {
            $this->sendOrderSms($order, $log->event_type);
        }
    }

    private function formatPhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '07')) {
            $phone = '94' . substr($phone, 1);
        }
        if (strlen($phone) < 10) {
            return null;
        }
        return '+' . $phone; // text.lk explicitly likes +94 or just 94, +94 is safest standard
    }
}

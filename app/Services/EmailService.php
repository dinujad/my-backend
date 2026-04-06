<?php

namespace App\Services;

use App\Models\Order;
use App\Models\EmailLog;
use App\Mail\OrderEventMail;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send an email based on order event.
     * 
     * @param Order $order
     * @param string $eventType e.g., 'created', 'shipped', 'completed'
     */
    public function sendOrderEmail(Order $order, string $eventType)
    {
        $toEmail = $order->customer->email ?? $order->shipping_address['email'] ?? null;
        
        if (!$toEmail) {
            return;
        }

        $subject = "Order {$order->order_number} Update - PrintWorksLK";
        
        try {
            Mail::to($toEmail)->send(new OrderEventMail($order, $eventType));
            
            $isSent = true;
            $error = null;
        } catch (\Exception $e) {
            $isSent = false;
            $error = $e->getMessage();
            Log::error("Email sending failed for order {$order->id}: {$error}");
        }

        EmailLog::create([
            'order_id' => $order->id,
            'to_email' => $toEmail,
            'subject' => $subject,
            'event_type' => $eventType,
            'is_sent' => $isSent,
            'error_message' => $error,
            'sent_at' => $isSent ? now() : null,
        ]);
    }

    public function resendEmail(int $emailLogId)
    {
        $log = EmailLog::findOrFail($emailLogId);
        $order = $log->order;
        
        if ($order) {
            $this->sendOrderEmail($order, $log->event_type);
        }
    }

    public function sendWelcomeEmail($user)
    {
        if (!$user->email) return;

        try {
            Mail::to($user->email)->send(new WelcomeMail($user->name));
        } catch (\Exception $e) {
            Log::error("Welcome email failed for {$user->email}: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Mail;

use App\Models\ChatConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatTransferred extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $conversation;
    public $reason;

    public function __construct(ChatConversation $conversation, string $reason)
    {
        $this->conversation = $conversation;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔄 Chat Transferred to You — ' . ($this->conversation->customer_name ?? 'Guest'),
        );
    }

    public function content(): Content
    {
        $name    = htmlspecialchars($this->conversation->customer_name ?? 'Guest');
        $email   = htmlspecialchars($this->conversation->customer_email ?? 'Not provided');
        $reason  = htmlspecialchars($this->reason);
        $dashUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/') . '/admin/live-chat';
        $time    = now()->setTimezone('Asia/Colombo')->format('D, d M Y h:i A');

        return new Content(
            htmlString: <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:40px 0;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <!-- Header -->
        <tr>
          <td style="background:#1a1a2e;padding:28px 32px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">
              PrintWorks <span style="color:#E64C3C;">Support</span>
            </h1>
            <p style="margin:6px 0 0;color:#9ca3af;font-size:13px;">Chat Transfer Notification</p>
          </td>
        </tr>
        <!-- Alert Banner -->
        <tr>
          <td style="background:#2563eb;padding:14px 32px;">
            <p style="margin:0;color:#ffffff;font-size:14px;font-weight:700;text-align:center;">
              🔄 &nbsp;A chat has been transferred to you
            </p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:32px;">
            <h2 style="margin:0 0 20px;color:#111827;font-size:18px;font-weight:700;">Chat Details</h2>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                  <span style="color:#6b7280;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Customer</span><br>
                  <span style="color:#111827;font-size:15px;font-weight:600;margin-top:4px;display:block;">{$name}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                  <span style="color:#6b7280;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Customer Email</span><br>
                  <span style="color:#111827;font-size:15px;margin-top:4px;display:block;">{$email}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                  <span style="color:#6b7280;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Transfer Reason</span><br>
                  <span style="color:#111827;font-size:15px;margin-top:4px;display:block;">{$reason}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;">
                  <span style="color:#6b7280;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Transferred At</span><br>
                  <span style="color:#111827;font-size:15px;margin-top:4px;display:block;">{$time} (Sri Lanka Time)</span>
                </td>
              </tr>
            </table>
            <!-- CTA -->
            <div style="margin-top:28px;text-align:center;">
              <a href="{$dashUrl}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:15px;font-weight:700;letter-spacing:0.2px;">
                Open Chat Dashboard →
              </a>
            </div>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background:#f9fafb;padding:20px 32px;text-align:center;border-top:1px solid #e5e7eb;">
            <p style="margin:0;color:#9ca3af;font-size:12px;">This is an automated notification from PrintWorks Support System.</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML,
        );
    }
}

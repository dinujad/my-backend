<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatAssignment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSupportChat;
use App\Mail\ChatTransferred;

class ChatService
{
    /**
     * Start a new conversation or resume an existing active one for a guest/user.
     */
    public function startConversation(string $sessionId, ?int $customerId, ?string $name, ?string $email, ?string $phone, ?int $orderId): ChatConversation
    {
        // Find existing active/waiting conversation
        $conversation = ChatConversation::where(function ($q) use ($sessionId, $customerId) {
            $q->where('session_id', $sessionId);
            if ($customerId) {
                $q->orWhere('customer_id', $customerId);
            }
        })
        ->whereIn('status', ['waiting', 'assigned', 'active', 'transferred'])
        ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new
        $conversation = ChatConversation::create([
            'session_id' => $sessionId,
            'customer_id' => $customerId,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'order_id' => $orderId,
            'status' => 'waiting',
            'last_activity_at' => now(),
        ]);

        // Notify admins — collect unique, valid email addresses
        $adminEmailList = User::whereIn('role', ['admin', 'super_admin', 'chat_manager'])
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn($e) => str_contains($e, '@'))
            ->values()
            ->toArray();

        // Also include the dedicated notification address from .env (if set & different)
        $notifyEmail = env('CHAT_NOTIFICATION_EMAIL');
        if ($notifyEmail && !in_array($notifyEmail, $adminEmailList, true)) {
            $adminEmailList[] = $notifyEmail;
        }

        if (!empty($adminEmailList)) {
            try {
                Mail::to($adminEmailList)->send(new NewSupportChat($conversation));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send new chat email: " . $e->getMessage());
            }
        }

        return $conversation;
    }

    /**
     * Send a message from a customer.
     */
    public function sendCustomerMessage(ChatConversation $conversation, string $message, ?int $senderId = null): ChatMessage
    {
        $conversation->update(['last_activity_at' => now()]);
        
        $msg = $conversation->messages()->create([
            'sender_type' => 'customer',
            'sender_id' => $senderId,
            'message' => $message,
            'is_read' => false,
        ]);

        return $msg;
    }

    /**
     * Send a message from an agent.
     */
    public function sendAgentMessage(ChatConversation $conversation, string $message, int $agentId): ChatMessage
    {
        // Auto-claim if still waiting
        if ($conversation->status === 'waiting') {
            $this->assignAgent($conversation, $agentId);
        }

        // Upgrade assigned/transferred → active on first reply
        if (in_array($conversation->status, ['assigned', 'transferred'], true)) {
            $conversation->update([
                'status' => 'active',
                'last_activity_at' => now(),
            ]);
        } else {
            $conversation->update(['last_activity_at' => now()]);
        }

        $msg = $conversation->messages()->create([
            'sender_type' => 'agent',
            'sender_id'   => $agentId,
            'message'     => $message,
            'is_read'     => true,
        ]);

        // Mark all unread customer messages as read
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $msg;
    }

    /**
     * Assign / Claim a chat — sets status to active immediately.
     */
    public function assignAgent(ChatConversation $conversation, int $agentId, ?int $assignedById = null): void
    {
        $conversation->update([
            'assigned_agent_id' => $agentId,
            'status'            => 'active',
            'last_activity_at'  => now(),
        ]);

        ChatAssignment::create([
            'chat_conversation_id' => $conversation->id,
            'assigned_agent_id' => $agentId,
            'assigned_by_id' => $assignedById ?? $agentId,
            'transfer_reason' => 'Initial Claim',
        ]);
        
        $agent = User::find($agentId);
        $this->createSystemMessage($conversation, "Agent {$agent->name} has joined the chat.");
    }

    /**
     * Transfer chat
     */
    public function transferChat(ChatConversation $conversation, int $newAgentId, int $assignedById, string $reason): void
    {
        $conversation->update([
            'assigned_agent_id' => $newAgentId,
            'status' => 'transferred'
        ]);

        ChatAssignment::create([
            'chat_conversation_id' => $conversation->id,
            'assigned_agent_id' => $newAgentId,
            'assigned_by_id' => $assignedById,
            'transfer_reason' => $reason,
        ]);
        
        $agent = User::find($newAgentId);
        $this->createSystemMessage($conversation, "Chat transferred to {$agent->name}. Reason: {$reason}");
        
        if ($agent && $agent->email) {
            try {
                Mail::to($agent->email)->send(new ChatTransferred($conversation, $reason));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send chat transfer email: " . $e->getMessage());
            }
        }
    }
    
    public function closeConversation(ChatConversation $conversation, int $agentId): void
    {
         $conversation->update(['status' => 'closed']);
         $this->createSystemMessage($conversation, "Chat was closed by the agent.");
    }

    public function createSystemMessage(ChatConversation $conversation, string $message): ChatMessage
    {
        return $conversation->messages()->create([
            'sender_type' => 'system',
            'message' => $message,
            'is_read' => false,
        ]);
    }
}

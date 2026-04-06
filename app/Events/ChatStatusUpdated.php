<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;

    public function __construct(ChatConversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->conversation->session_id),
            new PrivateChannel('admin.chat'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversation->id,
            'session_id' => $this->conversation->session_id,
            'status' => $this->conversation->status,
            'assigned_agent_id' => $this->conversation->assigned_agent_id,
        ];
    }
}

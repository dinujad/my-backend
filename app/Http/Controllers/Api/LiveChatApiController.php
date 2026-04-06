<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use App\Models\ChatConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Events\ChatMessageSent;
use App\Events\ChatStatusUpdated;

class LiveChatApiController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function init(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'order_id' => 'nullable|integer',
        ]);

        $customerId = auth('sanctum')->check() && auth('sanctum')->user()->customer 
            ? auth('sanctum')->user()->customer->id 
            : null;

        $conversation = $this->chatService->startConversation(
            $request->session_id,
            $customerId,
            $request->name,
            $request->email,
            $request->phone,
            $request->order_id
        );

        try {
            event(new ChatStatusUpdated($conversation));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Pusher/Event error: " . $e->getMessage());
        }

        return response()->json([
            'conversation' => $conversation->load(['messages', 'assignedAgent']),
        ]);
    }

    public function getHistory(Request $request, $sessionId)
    {
        $conversation = ChatConversation::with(['messages', 'assignedAgent'])
            ->where('session_id', $sessionId)
            ->first();

        if (!$conversation) {
            return response()->json(['messages' => []]);
        }

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    public function sendMessage(Request $request, $sessionId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $conversation = ChatConversation::where('session_id', $sessionId)->firstOrFail();

        $senderId = auth('sanctum')->check() && auth('sanctum')->user()->customer 
            ? auth('sanctum')->user()->customer->id 
            : null;

        $msg = $this->chatService->sendCustomerMessage($conversation, $request->message, $senderId);

        try {
            event(new ChatMessageSent($msg));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Pusher/Event error: " . $e->getMessage());
        }

        return response()->json(['message' => $msg]);
    }
    
    public function markRead(Request $request, $sessionId)
    {
        $conversation = ChatConversation::where('session_id', $sessionId)->firstOrFail();
        $conversation->messages()->where('sender_type', '!=', 'customer')->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use App\Events\ChatMessageSent;
use App\Events\ChatStatusUpdated;

class AdminLiveChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(Request $request)
    {
        // Eager-load the last message and unread count via withCount + relationship
        $conversations = ChatConversation::with([
                'assignedAgent:id,name',
                'customer:id,name',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->withCount([
                'messages as unread_count' => fn ($q) => $q->where('sender_type', 'customer')->where('is_read', false),
            ])
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->map(fn ($chat) => [
                'id'               => $chat->id,
                'session_id'       => $chat->session_id,
                'customer_name'    => $chat->customer_name ?? $chat->customer?->name ?? 'Guest',
                'customer_email'   => $chat->customer_email,
                'customer_phone'   => $chat->customer_phone,
                'status'           => $chat->status,
                'assigned_agent'   => $chat->assignedAgent?->name,
                'unread_count'     => $chat->unread_count ?? 0,
                'last_activity_at' => $chat->last_activity_at,
                'snippet'          => $chat->messages->first()?->message ?? '',
            ]);

        return response()->json($conversations);
    }

    public function show($id)
    {
        $conversation = ChatConversation::with([
                'messages' => fn ($q) => $q->orderBy('created_at'),
                'assignedAgent:id,name,email',
                'customer:id,name,email',
            ])
            ->findOrFail($id);

        // Mark customer messages as read when agent opens the chat
        $conversation->messages()->where('sender_type', 'customer')->where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'id'              => $conversation->id,
            'session_id'      => $conversation->session_id,
            'customer_name'   => $conversation->customer_name ?? $conversation->customer?->name ?? 'Guest',
            'customer_email'  => $conversation->customer_email ?? $conversation->customer?->email,
            'customer_phone'  => $conversation->customer_phone,
            'status'          => $conversation->status,
            'assigned_agent'  => $conversation->assignedAgent?->name,
            'last_activity_at'=> $conversation->last_activity_at,
            'messages'        => $conversation->messages->map(fn ($m) => [
                'id'          => $m->id,
                'sender_type' => $m->sender_type,
                'message'     => $m->message,
                'created_at'  => $m->created_at,
            ]),
        ]);
    }

    public function assign(Request $request, $id)
    {
        $conversation = ChatConversation::findOrFail($id);
        $agentId = $request->input('agent_id', auth()->id());
        
        $this->chatService->assignAgent($conversation, $agentId, auth()->id());
        
        try {
            event(new ChatStatusUpdated($conversation));
        } catch (\Exception $e) {}

        return response()->json(['success' => true, 'conversation' => $conversation]);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        
        $conversation = ChatConversation::findOrFail($id);
        
        $msg = $this->chatService->sendAgentMessage($conversation, $request->message, auth()->id());
        
        try {
            event(new ChatMessageSent($msg));
        } catch (\Exception $e) {}

        return response()->json(['success' => true, 'message' => $msg]);
    }
    
    public function transfer(Request $request, $id)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
            'reason' => 'required|string'
        ]);
        
        $conversation = ChatConversation::findOrFail($id);
        
        $this->chatService->transferChat($conversation, $request->agent_id, auth()->id(), $request->reason);
        
        try {
            event(new ChatStatusUpdated($conversation));
        } catch (\Exception $e) {}
        
        return response()->json(['success' => true]);
    }

    public function processClose(Request $request, $id) // `close` is reserved keyword in some contexts, but safe in JS, let's use processClose
    {
        $conversation = ChatConversation::findOrFail($id);
        $this->chatService->closeConversation($conversation, auth()->id());
        
        try {
            event(new ChatStatusUpdated($conversation));
        } catch (\Exception $e) {}
        
        return response()->json(['success' => true]);
    }
    
    public function agents()
    {
        $agents = User::whereIn('role', ['admin', 'super_admin', 'chat_manager', 'chat_agent'])->get(['id', 'name', 'email', 'role']);
        return response()->json($agents);
    }

    public function storeAgent(Request $request)
    {
        $user = $request->user();
        if (! $user || !in_array($user->role, ['admin', 'super_admin'], true)) {
            return response()->json(['message' => 'Only main admins can add new chat agents.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            // Model boot may override to 'customer'. Need to explicitly set or update.
        ]);
        
        // Since User::booted might set it back to customer, force the role:
        $user->role = 'chat_agent';
        $user->save();
        
        return response()->json(['success' => true, 'agent' => $user]);
    }
}

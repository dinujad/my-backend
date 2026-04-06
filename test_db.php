<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $conversations = \App\Models\ChatConversation::with(['assignedAgent', 'customer'])
        ->orderBy('last_activity_at', 'desc')
        ->get()
        ->map(function ($chat) {
            return [
                'id' => $chat->id,
                'customer_name' => $chat->customer_name ?? $chat->customer?->name ?? 'Guest',
                'status' => $chat->status,
                'snippet' => $chat->messages()->latest()->first()?->message ?? '',
            ];
        });

    echo json_encode($conversations, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

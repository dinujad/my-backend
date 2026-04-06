<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\ChatConversation::count();
    $chats = \App\Models\ChatConversation::all();
    echo "Total Conversations: " . $count . "\n";
    foreach ($chats as $chat) {
        echo "- ID: {$chat->id}, Session: {$chat->session_id}, Status: {$chat->status}, Messages: {$chat->messages()->count()}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

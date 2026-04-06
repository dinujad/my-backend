<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = \Illuminate\Http\Request::create('/api/admin/chat', 'GET');
    $controller = new \App\Http\Controllers\Api\AdminLiveChatController(app(\App\Services\ChatService::class));
    $response = $controller->index($request);
    echo json_encode($response->getData(true), JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = \App\Models\Category::all();
foreach($categories as $c) {
    echo "Category: " . $c->name . " | Active: " . ($c->is_active ?? 'N/A') . "\n";
}

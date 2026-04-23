<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Do NOT use realpath() here: if storage/framework/views does not exist yet
    | (fresh clone / Docker build), realpath() returns false and Blade throws
    | "Please provide a valid cache path." Laravel creates the directory when
    | compiling views.
    |
    */

    // Use ?: so an empty VIEW_COMPILED_PATH in .env does not become the Blade cache path.
    'compiled' => env('VIEW_COMPILED_PATH') ?: storage_path('framework/views'),

];

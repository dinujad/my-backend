<?php

return [

    'name' => env('LIGHTHOUSE_NAME', 'PrintWorks'),
    'schema_path' => base_path('graphql/schema.graphql'),
    'cache' => [
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', false),
        'key' => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),
        'ttl' => env('LIGHTHOUSE_CACHE_TTL', 3600),
    ],
    'namespaces' => [
        'models' => ['App\\Models'],
        'queries' => ['App\\GraphQL\\Queries'],
        'mutations' => ['App\\GraphQL\\Mutations'],
    ],
    'guard' => ['sanctum'],
    'route' => [
        'uri' => '/graphql',
        'name' => 'graphql',
        'middleware' => ['api'],
    ],

];

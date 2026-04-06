<?php

use Illuminate\Support\Str;

return [

    'domain' => env('HORIZON_DOMAIN', null),

    'path' => env('HORIZON_PATH', 'horizon'),

    'middleware' => ['web'],

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    'fast_termination' => false,

    'memory_limit' => 64,

    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'autoScaling' => [
                'minProcesses' => 1,
                'maxProcesses' => 10,
                'balanceTime' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
            ],
            'tries' => 3,
            'maxTime' => 0,
            'maxJobs' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'maxProcesses' => 10,
                'balanceMaxShift' => 2,
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 3,
            ],
        ],
    ],

];

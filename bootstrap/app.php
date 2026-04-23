<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // Plain /up avoids Laravel's health view + DiagnosingHealth pipeline, which can 500 in
        // Docker/Coolify when env, extensions, or cached state differ from local (large HTML body).
        health: null,
        then: function () {
            Route::get('/up', function () {
                return response('OK', 200)->header('Content-Type', 'text/plain; charset=UTF-8');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        $middleware->preventRequestsDuringMaintenance(except: [
            '/up',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

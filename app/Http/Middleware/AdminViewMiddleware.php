<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminViewMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canViewOperations()) {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to view orders or quotes.');
        }

        return $next($request);
    }
}

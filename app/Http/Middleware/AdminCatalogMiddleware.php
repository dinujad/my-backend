<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCatalogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageProducts()) {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to manage products.');
        }

        return $next($request);
    }
}

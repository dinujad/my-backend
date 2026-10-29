<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPanelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessAdmin()) {
            if ($user) {
                Auth::guard('web')->logout();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'You do not have access to the admin panel.',
            ]);
        }

        return $next($request);
    }
}

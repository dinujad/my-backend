<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatStaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $request->user()->isChatStaff()) {
            return response()->json(['message' => 'Unauthorized. Access restricted to chat staff.'], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminStaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageStaff()) {
            return redirect()->route('admin.dashboard')->with('error', 'Only an admin can manage staff roles.');
        }

        return $next($request);
    }
}

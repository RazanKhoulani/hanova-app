<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderStaffRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            !$user
            || (
                !$user->hasRole('admin')
                && !$user->hasRole('doctor')
                && !$user->hasRole('delivery')
            )
        ) {
            abort(403);
        }

        return $next($request);
    }
}

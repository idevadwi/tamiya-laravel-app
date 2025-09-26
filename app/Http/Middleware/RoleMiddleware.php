<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }

        // Check if user has at least one of the allowed roles
        if (! $user->roles()->whereIn('role_name', $roles)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: insufficient permissions',
            ], 403);
        }

        return $next($request);
    }
}

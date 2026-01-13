<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WebRoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Ensure roles are loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        // Handle comma-separated roles (e.g., "ADMINISTRATOR,MODERATOR")
        $allowedRoles = [];
        foreach ($roles as $role) {
            // Split by comma if it contains commas
            if (strpos($role, ',') !== false) {
                $allowedRoles = array_merge($allowedRoles, array_map('trim', explode(',', $role)));
            } else {
                $allowedRoles[] = trim($role);
            }
        }
        
        // Remove duplicates and filter empty values
        $allowedRoles = array_filter(array_unique($allowedRoles));

        // Check if user has at least one of the allowed roles
        if (empty($allowedRoles) || ! $user->hasAnyRole($allowedRoles)) {
            return redirect()->route('home')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

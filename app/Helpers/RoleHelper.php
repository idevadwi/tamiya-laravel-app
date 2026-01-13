<?php

if (!function_exists('isAdmin')) {
    /**
     * Check if the current authenticated user is an admin
     */
    function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->hasRole('ADMINISTRATOR');
    }
}

if (!function_exists('isModerator')) {
    /**
     * Check if the current authenticated user is a moderator
     */
    function isModerator(): bool
    {
        return auth()->check() && auth()->user()->hasRole('MODERATOR');
    }
}

if (!function_exists('hasRole')) {
    /**
     * Check if the current authenticated user has a specific role
     */
    function hasRole(string $role): bool
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }
}

if (!function_exists('hasAnyRole')) {
    /**
     * Check if the current authenticated user has any of the given roles
     */
    function hasAnyRole(array $roles): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole($roles);
    }
}

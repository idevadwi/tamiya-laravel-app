<?php

// ✅ add these imports
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth:sanctum' => EnsureFrontendRequestsAreStateful::class,
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'role.web'    => \App\Http\Middleware\WebRoleMiddleware::class,
            'tournament.context' => \App\Http\Middleware\SetTournamentContext::class,
        ]);

        // Apply tournament context middleware to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetTournamentContext::class,
        ]);

        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

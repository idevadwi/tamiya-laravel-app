<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define gate for administrator access
        Gate::define('isAdministrator', function ($user) {
            return $user->hasRole('ADMINISTRATOR');
        });

        // Define gate for tournament access (Admin or Moderator)
        Gate::define('accessTournament', function ($user) {
            return $user->hasAnyRole(['ADMINISTRATOR', 'MODERATOR']);
        });
    }
}

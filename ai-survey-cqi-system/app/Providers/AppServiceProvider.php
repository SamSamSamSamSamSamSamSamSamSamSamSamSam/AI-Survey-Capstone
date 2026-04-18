<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\SettingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use the Bootstrap 5 paginator (always safe)
        Paginator::useBootstrapFive();

        // Only run database-dependent logic if NOT running in the console (Terminal)
        if (!app()->runningInConsole()) {
            try {
                // Fetch the lifetime from the database
                $lifetime = setting('security.session_lifetime', 120);
                
                // Set the configuration dynamically
                Config::set('session.lifetime', $lifetime);
            } catch (\Exception $e) {
                // Fallback to default if the table isn't ready or error occurs
                Config::set('session.lifetime', 120);
            }
        }
    }
}

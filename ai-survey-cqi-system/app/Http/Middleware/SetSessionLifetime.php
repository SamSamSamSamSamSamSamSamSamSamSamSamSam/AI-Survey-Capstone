<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class SetSessionLifetime
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only run if the application is not running in the terminal
        if (!app()->runningInConsole()) {
            try {
                // Fetch from your settings table
                // If the 'setting' helper is globally available, use it.
                // Otherwise, use: DB::table('settings')->where('key', 'security.session_lifetime')->value('value')
                $lifetime = setting('security.session_lifetime', 120);
                
                // Dynamically override the session lifetime for this request
                config(['session.lifetime' => (int) $lifetime]);
            } catch (\Exception $e) {
                // Fallback to default if database is unavailable
                config(['session.lifetime' => 120]);
            }
        }

        return $next($request);
    }
}
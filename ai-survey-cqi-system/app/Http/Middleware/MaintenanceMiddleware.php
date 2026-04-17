<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceMiddleware
{
public function handle(Request $request, Closure $next)
{
    $settings = app(\App\Services\SettingService::class);
    $isMaintenanceMode = $settings->get('maintenance.mode', false);

    if (!$isMaintenanceMode) {
        return $next($request);
    }

    // Always allow the login page and logout logic
    if ($request->is('login', 'logout', 'admin/login*')) {
        return $next($request);
    }

    // AUTH CHECK: Must be logged in AND have the admin role
    if (Auth::check() && Auth::user()->hasRole('admin')) {
        return $next($request);
    }

    // Show maintenance page for everyone else (Guests and non-admin Users)
    $message = $settings->get('maintenance.message', 'The system is currently under maintenance.');
    return response()->view('errors.maintenance', compact('message'), 503);
}
}

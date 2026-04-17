<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $isMaintenanceMode = app(\App\Services\SettingService::class)
            ->get('maintenance.mode', false);

        if ($isMaintenanceMode && Auth::check() && ! Auth::user()->hasRole('admin')) {
            $message = app(\App\Services\SettingService::class)
                ->get('maintenance.message', 'The system is currently under maintenance.');

            return response()->view('errors.maintenance', compact('message'), 503);
        }

        return $next($request);
    }
}

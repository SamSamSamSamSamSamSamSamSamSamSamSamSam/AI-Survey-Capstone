<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,faculty')   // passes if user has ANY of the listed roles
     * 
     * PERFORMANCE: Caches user roles to reduce database queries
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // PERFORMANCE FIX: Cache user roles for 1 hour to reduce per-request queries
        $userRoles = Cache::remember(
            "user_roles_{$user->id}",
            3600, // 1 hour
            fn() => $user->roles()->pluck('name')->toArray()
        );

        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this page.');
    }
}

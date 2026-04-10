<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Protect a route by requiring the authenticated user to have a specific role.
     *
     * Role names must match the 'name' column on the roles table (lowercase):
     *   'admin', 'teacher', 'student'
     *
     * Usage in routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:teacher')
     *   ->middleware('role:student')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole($role)) {
            return $next($request);
        }

        abort(403, 'Unauthorized.');
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
         $user = auth()->user();

        // Ensure roles are loaded before checking
        if ($user && $user->roles()->where('name', $role)->exists()) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {

            $user = Auth::user();

            // Use your User model helper
            return redirect()->to($user->dashboardRoute());
        }

        return $next($request);
    }
}
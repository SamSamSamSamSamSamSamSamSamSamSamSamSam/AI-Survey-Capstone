<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $timeout = 120 * 60; // 2 hours in seconds
            $lastActivity = session('last_activity', time());
            if (time() - $lastActivity > $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/')->with('error', 'Session expired. Please login again.');
            }
            session(['last_activity' => time()]);
        }
        return $next($request);
    }
}

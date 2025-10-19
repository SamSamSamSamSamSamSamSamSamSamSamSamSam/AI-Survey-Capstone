<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckOnboarding
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Skip for admins
        if ($user->roles()->where('name', 'admin')->exists()) {
            return $next($request);
        }

        // Check if onboarded (has subjects)
        $hasSubjects = false;

        if ($user->roles()->where('name', 'student')->exists()) {
            $hasSubjects = $user->enrolledSubjects()->exists();
        } elseif ($user->roles()->where('name', 'teacher')->exists()) {
            $hasSubjects = $user->teachingSubjects()->exists();
        }

        if (!$hasSubjects && !$request->is('onboarding/*')) {
            return redirect()->route('onboarding.upload');
        }

        return $next($request);
    }
}

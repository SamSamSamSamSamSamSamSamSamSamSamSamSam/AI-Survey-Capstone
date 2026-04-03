<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Semester;

class CheckOnboarding
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Admins always pass
        if ($user->roles()->where('name', 'admin')->exists()) {
            return $next($request);
        }

        $activeSemester = Semester::getActive();

        // If there is no active semester, let them through —
        // there's nothing to onboard into yet.
        if (!$activeSemester) {
            return $next($request);
        }

        // Check if user has subjects for the ACTIVE semester specifically
        $hasSubjects = $user->hasSubjectsForSemester($activeSemester->id);

        if (!$hasSubjects && !$request->is('onboarding/*')) {
            return redirect()->route('onboarding.upload');
        }

        return $next($request);
    }
}
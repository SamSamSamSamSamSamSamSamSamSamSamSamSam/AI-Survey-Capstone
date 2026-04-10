<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Semester;

class CheckOnboarding
{
    /**
     * Ensure teachers and students have been assigned to at least one
     * course offering in the active semester before accessing protected routes.
     *
     * Admins always pass through.
     * If there is no active semester, everyone passes through — nothing to onboard into.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $activeSemester = Semester::getActive();

        if (! $activeSemester) {
            return $next($request);
        }

        if (! $user->hasSubjectsForSemester($activeSemester->id)
            && ! $request->is('onboarding/*')
        ) {
            return redirect()->route('onboarding.upload');
        }

        return $next($request);
    }
}
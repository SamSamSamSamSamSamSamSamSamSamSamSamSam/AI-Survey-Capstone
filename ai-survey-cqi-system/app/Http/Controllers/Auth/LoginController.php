<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS   = 5;
    private const DECAY_SECONDS  = 60;

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        // Detect field: email format → use email column, otherwise user_id_number
        $field       = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_id_number';
        $credentials = [$field => $request->login, 'password' => $request->password];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        // Check if they came from a CSV import and haven't set a password yet
        if ($user->must_change_password) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'login' => 'Your account is not fully set up. Please check your email to set your password.',
            ]);
        }

        // Block unverified users
        if (! Auth::user()->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'login' => 'Your email address is not verified. Please check your inbox.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->dashboardRoute());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // -------------------------------------------------------------------------
    // Rate limiting helpers
    // -------------------------------------------------------------------------

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'login' => "Too many login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('login')) . '|' . $request->ip();
    }
}

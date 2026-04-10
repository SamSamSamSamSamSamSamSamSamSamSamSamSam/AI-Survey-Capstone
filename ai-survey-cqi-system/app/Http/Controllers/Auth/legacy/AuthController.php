<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Auth\LoginRequest;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\View\View;

    class AuthController extends Controller
    {
        // ── Show login form ────────────────────────────────────────────────────────

        public function showLogin(): View
        {
            return view('auth.login');
        }

        // ── Handle login submission ────────────────────────────────────────────────

        /**
         * Authenticate the user and redirect based on their role.
         *
         * Rate limiting and credential resolution are handled by LoginRequest.
         * Role-based redirect order: admin → teacher → student.
         */
        public function login(LoginRequest $request): RedirectResponse
        {
            $user = $request->authenticate();

            $request->session()->regenerate();

            return $this->redirectByRole($user);
        }

        // ── Handle logout ──────────────────────────────────────────────────────────

        public function logout(Request $request): RedirectResponse
        {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        // ── Helpers ────────────────────────────────────────────────────────────────

        /**
         * Redirect the user to their role-specific dashboard.
         * Roles are checked in priority order: admin first, then teacher, then student.
         */
        private function redirectByRole($user): RedirectResponse
        {
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('teacher')) {
                return redirect()->route('teacher.dashboard');
            }

            return redirect()->route('student.dashboard');
        }
    }
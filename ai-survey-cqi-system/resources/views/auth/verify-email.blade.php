<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email — CQI System</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>

<div class="auth-page auth-page--centered">

    {{-- Theme toggle --}}
    <div class="auth-theme-toggle auth-theme-toggle--fixed">
        <div class="theme-switch" title="Toggle dark mode">
            <label class="switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider">
                    <i class="bi bi-sun-fill icon sun"></i>
                    <i class="bi bi-moon-fill icon moon"></i>
                </span>
            </label>
        </div>
    </div>

    <div class="auth-verify-card">

        {{-- Icon --}}
        <div class="auth-verify-icon">
            <i class="bi bi-envelope-check"></i>
        </div>

        {{-- Brand --}}
        <div class="auth-mobile-brand mb-3">
            <i class="bi bi-mortarboard-fill"></i>
            CQI System
        </div>

        <h1 class="auth-verify-title">Check your inbox</h1>

        <p class="auth-verify-body">
            We sent a verification link to<br>
            <strong>{{ auth()->user()->email }}</strong>
        </p>

        <p class="auth-verify-hint">
            Click the link in that email to activate your account.
            If you don't see it, check your spam folder.
        </p>

        {{-- Success flash --}}
        @if(session('status'))
            <div class="alert alert-success w-100 text-start" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>
                {{ session('status') }}
            </div>
        @endif

        {{-- Resend --}}
        <form method="POST" action="{{ route('verification.send') }}" class="w-100 mb-2">
            @csrf
            <button type="submit" class="btn btn-primary w-100 auth-submit-btn">
                <i class="bi bi-send me-2"></i> Resend Verification Email
            </button>
        </form>

        {{-- Sign out --}}
        <form method="POST" action="{{ route('logout') }}" class="w-100">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
                Sign out
            </button>
        </form>

    </div>

</div>

<script>
(function () {
    const THEME_KEY = 'cqi-theme';
    const html   = document.documentElement;
    const toggle = document.getElementById('themeToggle');
    const saved  = localStorage.getItem(THEME_KEY) || 'light';
    html.setAttribute('data-bs-theme', saved);
    if (toggle) toggle.checked = (saved === 'dark');
    if (toggle) {
        toggle.addEventListener('change', function () {
            const next = this.checked ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem(THEME_KEY, next);
        });
    }
})();
</script>

</body>
</html>
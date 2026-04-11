@extends('layouts.login')
@section('title', 'Sign In')

@push('styles')
{{-- Auth page overrides: no sidebar shell on this page --}}
<style>
    .app-wrapper { display: block; }
    .sidebar, .topbar { display: none !important; }
    .main { margin-left: 0 !important; }
    .content { padding: 0 !important; }
</style>
@endpush

@section('content')

<div class="auth-page">

    <div class="auth-split-card">

        {{-- ===== LEFT PANEL ===== --}}
        <div class="auth-left" aria-hidden="true">

            {{-- Decorative grid --}}
            <div class="auth-left-grid"></div>

            <div class="auth-left-body">

                <div class="auth-left-brand">
                    <i class="bi bi-mortarboard-fill"></i>
                    CQI System
                </div>

                <div class="auth-left-headline">
                    <h2>Continuous Quality<br>Improvement</h2>
                    <p>
                        A smart survey platform designed to
                        help faculty, students, and administrators
                        drive meaningful academic improvement.
                    </p>
                </div>

                <div class="auth-left-pills">
                    <span class="auth-pill"><i class="bi bi-check2"></i> AI-Powered Analysis</span>
                    <span class="auth-pill"><i class="bi bi-check2"></i> Role-Based Access</span>
                    <span class="auth-pill"><i class="bi bi-check2"></i> CQI Report Generation</span>
                </div>

            </div>

        </div>

        {{-- ===== RIGHT PANEL (form) ===== --}}
        <div class="auth-right">

            {{-- Theme toggle top-right --}}
            <div class="auth-theme-toggle">
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

            <div class="auth-right-inner">

                {{-- Logo (mobile only) --}}
                <div class="auth-mobile-brand d-lg-none">
                    <i class="bi bi-mortarboard-fill"></i>
                    CQI System
                </div>

                <div class="auth-form-header">
                    <h1>Welcome back</h1>
                    <p>Sign in to your account to continue.</p>
                </div>

                {{-- Alerts --}}
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->has('login'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ $errors->first('login') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf

                    {{-- Identifier --}}
                    <div class="mb-3">
                        <label class="form-label" for="login">Email or ID Number</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-person input-icon"></i>
                            <input
                                type="text"
                                class="form-control auth-input @error('login') is-invalid @enderror"
                                name="login"
                                id="login"
                                placeholder="you@example.com or 2021-XXXXX"
                                value="{{ old('login') }}"
                                autocomplete="username"
                                autofocus
                                required
                            >
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-lock input-icon"></i>
                            <input
                                type="password"
                                class="form-control auth-input @error('password') is-invalid @enderror"
                                name="password"
                                id="password"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="input-icon-right toggle-password"
                                    data-target="password" aria-label="Toggle password visibility"
                                    tabindex="-1">
                                <i class="bi bi-eye" id="pwIcon"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox"
                                   id="remember" name="remember">
                            <label class="form-check-label small" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary w-100 auth-submit-btn" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Signing in…
                        </span>
                    </button>

                </form>

                <p class="auth-footer-note">
                    Your account is provided by your system administrator.
                </p>

            </div>{{-- /.auth-right-inner --}}

        </div>{{-- /.auth-right --}}

    </div>{{-- /.auth-split-card --}}

</div>{{-- /.auth-page --}}

@endsection

@push('scripts')
<script src="{{ asset('js/modules/auth.js') }}"></script>
@endpush
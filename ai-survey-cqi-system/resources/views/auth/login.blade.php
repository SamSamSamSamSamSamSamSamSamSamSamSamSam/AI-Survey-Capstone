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

            {{-- Decorative glow orbs --}}
            <div class="auth-left-orb auth-left-orb--1"></div>
            <div class="auth-left-orb auth-left-orb--2"></div>

            <div class="auth-left-body">

                <div class="auth-left-brand">
                    <i class="bi bi-mortarboard-fill"></i>
                    {{ setting('app.name') }}
                </div>

                <div class="auth-left-headline">
                    <h2>AI-Driven Continuous<br>Quality Improvement</h2>
                    <p>
                        An intelligent survey and analytics platform that transforms 
                        student feedback into actionable insights through sentiment analysis, 
                        statistical evaluation, and AI-generated recommendations.
                    </p>
                </div>

                <div class="auth-left-pills">
                    <span class="auth-pill">
                        <span class="auth-pill-icon"><i class="bi bi-stars"></i></span>
                        NLP Sentiment Analysis
                    </span>
                    <span class="auth-pill">
                        <span class="auth-pill-icon"><i class="bi bi-shield-check"></i></span>
                        Statistical Evaluation
                    </span>
                    <span class="auth-pill">
                        <span class="auth-pill-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                        AI CQI Recommendations
                    </span>
                </div>

                {{-- Glassmorphic feature card slideshow --}}
                <div class="auth-feature-slideshow" id="authFeatureSlideshow">
                    <div class="auth-feature-slides-wrap">

                        <div class="auth-feature-slide is-active">
                            <div class="auth-feature-card">
                                <div class="auth-feature-card-icon">
                                    <i class="bi bi-cpu"></i>
                                </div>
                                <div class="auth-feature-card-body">
                                    <div class="auth-feature-card-title">Sentiment Analysis</div>
                                    <div class="auth-feature-card-desc">Classifies open-ended responses using NLP models for deeper insights</div>
                                </div>
                            </div>
                        </div>

                        <div class="auth-feature-slide">
                            <div class="auth-feature-card">
                                <div class="auth-feature-card-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="auth-feature-card-body">
                                    <div class="auth-feature-card-title">Survey Analytics</div>
                                    <div class="auth-feature-card-desc">Computes mean, frequency, and performance trends from structured responses</div>
                                </div>
                            </div>
                        </div>

                        <div class="auth-feature-slide">
                            <div class="auth-feature-card">
                                <div class="auth-feature-card-icon">
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                </div>
                                <div class="auth-feature-card-body">
                                    <div class="auth-feature-card-title">AI Recommendations</div>
                                    <div class="auth-feature-card-desc"> Generates CQI reports with actionable insights using generative AI</div>
                                </div>
                            </div>
                        </div>

                        <div class="auth-feature-slide">
                            <div class="auth-feature-card">
                                <div class="auth-feature-card-icon">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div class="auth-feature-card-body">
                                    <div class="auth-feature-card-title">Performance Tracking</div>
                                    <div class="auth-feature-card-desc">Monitor trends and changes in faculty performance across survey periods</div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /.auth-feature-slides-wrap --}}

                    <div class="auth-feature-dots" role="tablist" aria-label="Feature slideshow">
                        <button class="auth-feature-dot is-active" data-index="0" aria-label="Slide 1" role="tab" aria-selected="true"></button>
                        <button class="auth-feature-dot" data-index="1" aria-label="Slide 2" role="tab" aria-selected="false"></button>
                        <button class="auth-feature-dot" data-index="2" aria-label="Slide 3" role="tab" aria-selected="false"></button>
                        <button class="auth-feature-dot" data-index="3" aria-label="Slide 4" role="tab" aria-selected="false"></button>
                    </div>

                </div>{{-- /.auth-feature-slideshow --}}

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

                {{-- Department brand (always visible) --}}
                <div class="auth-dept-brand">
                    <div class="auth-dept-logo-row">
                        <span class="auth-dept-name">DCISM</span>
                        <img src="{{ asset('images/dcism_logo.png') }}"
                             alt="DCISM — Department of Computer, Information Sciences and Mathematics logo"
                             class="auth-dept-logo-img">
                    </div>
                    <p class="auth-dept-subtitle">Department of Computer, Information Sciences and Mathematics</p>
                </div>

                {{-- Logo (mobile only) --}}
                <div class="auth-mobile-brand d-lg-none">
                    <i class="bi bi-mortarboard-fill"></i>
                    {{ setting('app.name') }}
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
                                placeholder="you@usc.edu.ph or 201XXXXX"
                                value="{{ old('login') }}"
                                autocomplete="username"
                                autofocus
                                required
                            >
                        </div>
                        @error('login')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
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
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember me & Forgot Password --}}
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small" for="remember">
                                Remember me
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="small text-primary text-decoration-none fw-500" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary w-100 auth-submit-btn" id="loginBtn">
                        <span class="btn-text">
                            Sign In
                            <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Signing in…
                        </span>
                    </button>

                </form>

                <p class="auth-footer-note">
                    <i class="bi bi-info-circle me-1"></i>
                    Your account is provided by your system administrator.
                </p>

            </div>{{-- /.auth-right-inner --}}

        </div>{{-- /.auth-right --}}

    </div>{{-- /.auth-split-card --}}

</div>{{-- /.auth-page --}}

@endsection

@push('scripts')
@vite(['resources/js/modules/auth.js'])
@endpush

@extends('layouts.login')
@section('title', 'Forgot Password')

@push('styles')
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
            <div class="auth-left-grid"></div>
            <div class="auth-left-orb auth-left-orb--1"></div>
            <div class="auth-left-orb auth-left-orb--2"></div>

            <div class="auth-left-body">
                <div class="auth-left-brand">
                    <i class="bi bi-mortarboard-fill"></i> CQI System
                </div>
                <div class="auth-left-headline">
                    <h2>Account Recovery</h2>
                    <p>Enter your email address and we'll send you a secure link to reset your password and regain access to your CQI dashboard.</p>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT PANEL ===== --}}
        <div class="auth-right">
            <div class="auth-right-inner">
                <div class="auth-dept-brand">
                    <div class="auth-dept-logo-row">
                        <span class="auth-dept-name">DCISM</span>
                        <img src="{{ asset('images/dcism_logo.png') }}" alt="DCISM logo" class="auth-dept-logo-img">
                    </div>
                    <p class="auth-dept-subtitle">Department of Computer, Information Sciences and Mathematics</p>
                </div>

                <div class="auth-form-header">
                    <h1>Forgot Password?</h1>
                    <p>No worries! Enter your email to get back on track.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" name="email" id="email" 
                                   class="form-control auth-input @error('email') is-invalid @enderror" 
                                   placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 auth-submit-btn">
                        Send Reset Link <i class="bi bi-send ms-1"></i>
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
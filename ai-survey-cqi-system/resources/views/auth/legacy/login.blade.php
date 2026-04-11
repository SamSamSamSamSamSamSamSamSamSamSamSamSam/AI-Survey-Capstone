@extends('layouts.app1')

@section('title', 'Login')

@section('content')
<div class="container-fluid p-0">
    <!-- Main Auth Container -->
    <div class="auth-page d-flex min-vh-100 align-items-center justify-content-center">
        <div class="p-4" style="max-width: 1000px; width: 100%;">
            
            <!-- Auth Card -->
            <div class="card auth-card overflow-hidden">
                <div class="row g-0">
                    
                    <!-- Left Side - Welcome Section -->
                    <div class="col-lg-5 d-none d-lg-flex left-side align-items-center justify-content-center">
                        <div class="left-side-text text-center">
                            <h3 class="welcome-msg mb-2">Good to see you!</h3>
                            <p>Ready to evaluate?</p>
                        </div>
                    </div>

                    <!-- Right Side - Login Form -->
                    <div class="col-12 col-lg-7 right-side bg-white">
                        <div class="d-flex flex-column h-100">
                            
                            <!-- Logo Section -->
                            <div class="pt-5 mb-3 text-center">
                                <a href="javascript:void(0)" class="d-block auth-logo">
                                    <img src="{{ asset('assets/images/dcismicon.png') }}" alt="Logo" width="130">
                                </a>
                            </div>

                            <!-- Auth Content -->
                            <div class="auth-content my-auto p-4 p-md-5">
                                <div class="form-container slide-left" id="formContainer">
                                    <div class="form" id="form1">
                                        
                                        <!-- Header -->
                                        <div class="text-center">
                                            <h4 class="mt-2 text-dark">Sign in to continue.</h4>
                                        </div>
                                        {{-- Session status (e.g. after email verification) --}}
                                        @if (session('status'))
                                            <div class="alert alert-success">{{ session('status') }}</div>
                                        @endif

                                        {{-- Login / rate-limit errors --}}
                                        @if ($errors->has('login'))
                                            <div class="alert alert-error">{{ $errors->first('login') }}</div>
                                        @endif

                                        <!-- Session Messages -->
                                        {{-- @if (session('success'))
                                            <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
                                                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                                                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif --}}
                                                                        
                                        <!-- Login Form -->
                                        <form class="mt-4 pt-2" method="POST" action="{{ route('login') }}">
                                            @csrf
                                            
                                            <!-- Email Field -->
                                            <div class="mb-3">
                                                <label class="form-label" for="login">Email or ID Number</label>
                                                <input 
                                                    type="text"
                                                    class="form-control @error('login') is-invalid @enderror"
                                                    name="login"
                                                    id="login"
                                                    placeholder="you@example.com or 123456789"
                                                    value="{{ old('login') }}"
                                                    autocomplete="username"
                                                    autofocus
                                                    required
                                                >
                                                {{-- @error('login')
                                                    <div class="invalid-feedback">
                                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                                    </div>
                                                @enderror --}}
                                            </div>
                                            
                                            <!-- Password Field -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-grow-1">
                                                        <label class="form-label" for="password">Password</label>
                                                    </div>
                                                    {{-- Forgot Password Link (Commented Out) --}}
                                                    {{-- <div class="flex-shrink-0">
                                                        <div><a href="#" class="text-muted">Forgot password?</a></div>
                                                    </div> --}}
                                                </div>
                                                
                                                <div class="input-group auth-pass-inputgroup">
                                                    <input 
                                                        type="password" 
                                                        class="form-control @error('password') is-invalid @enderror" 
                                                        name="password" 
                                                        id="password" 
                                                        placeholder="••••••••" 
                                                        autocomplete="current-password"
                                                        required
                                                    >
                                                    <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </button>
                                                </div>
                                                {{-- @error('password')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror --}}
                                            </div>

                                            <!-- Remember Me Checkbox -->
                                            <div class="row mb-4">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               id="remember-check" 
                                                               name="remember">
                                                        <label class="form-check-label" for="remember-check">
                                                            Remember me
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="mb-3">
                                                <button class="btn btn-sign-in w-100 waves-effect waves-light" type="submit">
                                                    Sign In
                                                </button>
                                            </div>
                                            {{-- <div class="text-center mt-3">
                                                <p class="mb-0">
                                                    Don't have an account?
                                                    <a href="{{ route('signup') }}" class="text-primary fw-semibold">
                                                        Sign Up
                                                    </a>
                                                </p>
                                            </div> --}}
                                        </form>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Auth Card -->
            
        </div>
    </div>
    <!-- End Main Auth Container -->
</div>
@endsection
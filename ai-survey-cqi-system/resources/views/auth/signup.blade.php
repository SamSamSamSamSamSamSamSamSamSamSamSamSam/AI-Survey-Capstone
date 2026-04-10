@extends('layouts.app')

@section('title', 'Sign Up')

@section('header')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .signup-card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .form-floating > .form-control:focus ~ label { color: #0d6efd; }
        .password-requirements { font-size: 0.8rem; color: #6c757d; }
    </style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card signup-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">Join the AI Survey CQI System</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="signupForm" action="{{ route('signup.submit') }}" method="POST" novalidate>
                        @csrf
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" placeholder="John Doe" value="{{ old('name') }}" required>
                            <label for="name"><i class="bi bi-person me-2"></i>Full Name</label>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" placeholder="name@usc.edu.ph" value="{{ old('email') }}" required>
                            <label for="email"><i class="bi bi-envelope me-2"></i>Email Address</label>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="" selected disabled>Choose your role...</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                            </select>
                            <label for="role"><i class="bi bi-briefcase me-2"></i>Identify as</label>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-floating mb-2">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-3 px-1">
                            <ul class="password-requirements list-unstyled mb-0">
                                <li><i class="bi bi-info-circle me-1"></i> Min. 8 characters</li>
                                <li><i class="bi bi-info-circle me-1"></i> Include: A-Z, a-z, 0-9, and a symbol (@$!%*#?&)</li>
                            </ul>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
                            <label for="password_confirmation"><i class="bi bi-shield-check me-2"></i>Confirm Password</label>
                            <div class="invalid-feedback" id="passwordMatchError">
                                Passwords do not match.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                            Create Account
                        </button>
                    </form>

                    <div class="mt-4 text-center">
                        <span class="text-muted">Already have an account?</span> 
                        <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        'use strict'
        const form = document.getElementById('signupForm');
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const matchError = document.getElementById('passwordMatchError');

        form.addEventListener('submit', event => {
            let isValid = true;

            // Client-side Password Match check
            if (password.value !== confirm.value) {
                confirm.setCustomValidity('Invalid');
                confirm.classList.add('is-invalid');
                matchError.style.display = 'block';
                isValid = false;
            } else {
                confirm.setCustomValidity('');
                confirm.classList.remove('is-invalid');
                confirm.classList.add('is-valid');
                matchError.style.display = 'none';
            }

            if (!form.checkValidity() || !isValid) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);

        // Real-time listener to remove error as user types
        confirm.addEventListener('input', () => {
            if (password.value === confirm.value) {
                confirm.classList.remove('is-invalid');
                matchError.style.display = 'none';
            }
        });
    })()
</script>
@endsection
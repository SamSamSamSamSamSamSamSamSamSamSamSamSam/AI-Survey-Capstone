@extends('layouts.app')
@section('title', 'Activate Account')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">AI Survey CQI</h2>
            <p class="text-muted">Set your password to activate your account</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('password.update') }}" id="activationForm">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}" 
                               class="form-control bg-light @error('email') is-invalid @enderror" 
                               required readonly>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Create a strong password" required autofocus>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-shield-check"></i></span>
                            <input type="password" name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Repeat your password" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                            <span id="btnText">Activate Account</span>
                            <div id="spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="small text-muted">&copy; {{ date('Y') }} University of San Carlos - DCISM</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('activationForm').onsubmit = function() {
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const spinner = document.getElementById('spinner');

        btn.disabled = true;
        text.innerText = "Activating...";
        spinner.classList.remove('d-none');
    };
</script>
@endpush
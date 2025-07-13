@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="card mx-auto" style="max-width: 500px;">
    <div class="card-body">
        <h3 class="card-title mb-4">Login to Your Account</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif  
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 text-center">
            <p>Don't have an account? <a href="{{ route('signup') }}">Register here</a></p>
        </div>
    </div>
</div>
@endsection
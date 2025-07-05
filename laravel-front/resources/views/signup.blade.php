@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
<div class="card mx-auto" style="max-width: 500px;">
    <div class="card-body">
        <h3 class="card-title mb-4">Create an Account</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('signup.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>
    </div>
</div>
@endsection

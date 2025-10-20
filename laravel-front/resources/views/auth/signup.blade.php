@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
<div class="card mx-auto" style="max-width: 500px;">
    <div class="card-body">
        <h3 class="card-title mb-4">Create an Account</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('signup.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" name="password_confirmation" required>
            </div>

            <div class="mb-3">
                <label for="role">Role</label>
                <select name="role" class="form-control" required>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>

        <div class="mt-3 text-center">
            <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
    </div>
</div>
@endsection

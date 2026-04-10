@extends('layouts.default')

@section('content')
<div class="container py-5 text-center">
    <h3>Verify Your Email</h3>
    <p>We've sent a link to <strong>{{ auth()->user()->email }}</strong>. Please check your inbox.</p>
    
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-link">Resend Verification Email</button>
    </form>
</div>
@endsection
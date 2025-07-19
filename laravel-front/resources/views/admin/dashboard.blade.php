@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <h1>Welcome, Admin!</h1>
    <p>This is your control panel.</p>
    <p>Welcome, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

    <!-- Add invite faculty button here later -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

@endsection

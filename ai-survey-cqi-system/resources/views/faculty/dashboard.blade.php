@extends('layouts.app')
@section('title', 'Faculty Dashboard')

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
@endsection

@section('content')
<div class="topbar">
    <div>
        <h1>Dashboard <span class="badge">Faculty</span></h1>
        <p class="user-meta">{{ auth()->user()->name }} &nbsp;·&nbsp; {{ auth()->user()->user_id_number }}</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Sign Out</button>
    </form>
</div>

@if (session('status'))
    <div class="alert-success">{{ session('status') }}</div>
@endif

<div class="card">
    Faculty panel coming soon — your course offerings, surveys, and analytics will appear here.
</div>

@endsection

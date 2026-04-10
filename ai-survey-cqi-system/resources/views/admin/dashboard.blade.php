@extends('admin.layouts.app')
@section('title', 'Admin Dashboard')

@push('styles')
<style>
    /* body { font-family: sans-serif; background: #f1f5f9; padding: 2rem; } */
    .topbar { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .topbar h1 { font-size: 1.1rem; }
    .badge { background: #fee2e2; color: #dc2626; font-size: .72rem; font-weight: 700; padding: .2rem .55rem; border-radius: 999px; margin-left: .4rem; }
    .user-meta { font-size: .85rem; color: #6b7280; }
    .btn-logout { background: none; border: 1px solid #e5e7eb; padding: .4rem .9rem; border-radius: 6px; font-size: .85rem; cursor: pointer; color: #374151; }
    .btn-logout:hover { background: #f3f4f6; }
    .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); color: #6b7280; font-size: .9rem; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 7px; padding: .7rem 1rem; margin-bottom: 1.25rem; font-size: .875rem; }
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <h1>Dashboard <span class="badge">Admin</span></h1>
        <p class="user-meta">{{ auth()->user()->name }} &nbsp;·&nbsp; {{ auth()->user()->user_id_number }}</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Sign Out</button>
    </form>
</div>

<div class="card">
    Admin panel coming soon — user management, academic structure, and system settings will appear here.
</div>
@endsection

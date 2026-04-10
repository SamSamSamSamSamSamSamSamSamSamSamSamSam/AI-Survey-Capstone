{{--
    resources/views/admin/layouts/app.blade.php
    Usage: @extends('admin.layouts.app') @section('content') ... @endsection
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — CQI System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 220px; background: #1e1b4b; color: #c7d2fe; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 1.25rem 1rem; font-size: 1rem; font-weight: 700; color: #fff; border-bottom: 1px solid #312e81; }
        .sidebar-brand span { font-size: .7rem; display: block; color: #a5b4fc; font-weight: 400; }
        .sidebar-nav { flex: 1; padding: .75rem 0; }
        .nav-section { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: #6366f1; padding: .75rem 1rem .25rem; }
        .nav-link { display: block; padding: .5rem 1rem; font-size: .85rem; color: #c7d2fe; text-decoration: none; border-radius: 0; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: #312e81; color: #fff; }
        .sidebar-footer { padding: .75rem 1rem; border-top: 1px solid #312e81; font-size: .8rem; color: #a5b4fc; }

        /* Main */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-title { font-size: 1rem; font-weight: 600; }
        .topbar-user { font-size: .825rem; color: #6b7280; display: flex; align-items: center; gap: .75rem; }
        .btn-logout { background: none; border: 1px solid #e5e7eb; padding: .35rem .85rem; border-radius: 6px; font-size: .8rem; cursor: pointer; color: #374151; }
        .btn-logout:hover { background: #f3f4f6; }
        .content { flex: 1; padding: 1.75rem; overflow-y: auto; }

        /* Alerts */
        .alert { padding: .7rem 1rem; border-radius: 7px; font-size: .875rem; margin-bottom: 1.25rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

        /* Page header */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.25rem; }

        /* Card */
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
        .card-body { padding: 1.5rem; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead { background: #f9fafb; }
        th { padding: .65rem 1rem; text-align: left; font-weight: 600; color: #6b7280; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb; }
        td { padding: .7rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr.archived td { opacity: .55; }

        /* Buttons */
        .btn { display: inline-block; padding: .5rem 1rem; border-radius: 6px; font-size: .85rem; cursor: pointer; text-decoration: none; border: none; font-weight: 500; transition: background .15s; }
        .btn-sm { padding: .3rem .7rem; font-size: .78rem; }
        .btn-primary   { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger    { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #fecaca; }
        .btn-success   { background: #d1fae5; color: #065f46; }
        .btn-success:hover { background: #a7f3d0; }
        .btn-warning   { background: #fef3c7; color: #92400e; }
        .btn-warning:hover { background: #fde68a; }

        /* Badges */
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
        .badge-active   { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #f3f4f6; color: #6b7280; }
        .badge-archived { background: #fef3c7; color: #92400e; }

        /* Form */
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }
        .form-control { width: 100%; padding: .55rem .75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .9rem; }
        .form-control:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
        .form-control.is-invalid { border-color: #ef4444; }
        .form-text  { font-size: .78rem; color: #9ca3af; margin-top: .25rem; }
        .invalid-feedback { color: #dc2626; font-size: .8rem; margin-top: .25rem; }

        /* Filters */
        .filters { display: flex; gap: .65rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .filters .form-control { width: auto; }

        /* Actions cell */
        .actions { display: flex; gap: .35rem; flex-wrap: wrap; }

        /* Pagination */
        .pagination { display: flex; gap: .3rem; justify-content: flex-end; padding: .9rem 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .3rem .65rem; border-radius: 5px; font-size: .78rem; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; }
        .pagination a:hover { background: #f3f4f6; }
        .pagination .active { background: #4f46e5; color: #fff; border-color: #4f46e5; }

        .empty-state { text-align: center; padding: 3rem; color: #9ca3af; font-size: .9rem; }
    </style>
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        CQI System
        <span>Administrator</span>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Users</p>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">User Management</a>

        <p class="nav-section">Academic</p>
        <a href="{{ route('admin.programs.index') }}"  class="nav-link {{ request()->routeIs('admin.programs.*')  ? 'active' : '' }}">Programs</a>
        <a href="{{ route('admin.curricula.index') }}"  class="nav-link {{ request()->routeIs('admin.curricula.*')  ? 'active' : '' }}">Curricula</a>
        <a href="{{ route('admin.subjects.index') }}"  class="nav-link {{ request()->routeIs('admin.subjects.*')  ? 'active' : '' }}">Subjects</a>
        <a href="{{ route('admin.semesters.index') }}" class="nav-link {{ request()->routeIs('admin.semesters.*') ? 'active' : '' }}">Semesters</a>
        <a href="{{ route('admin.prospectus.index') }}" class="nav-link {{ request()->routeIs('admin.prospectus.*') ? 'active' : '' }}">Prospectus</a>
        <a href="{{ route('admin.offerings.index') }}" class="nav-link {{ request()->routeIs('admin.offerings.*') ? 'active' : '' }}">Course Offerings</a>

        <p class="nav-section">System</p>
        <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
    </nav>
    <div class="sidebar-footer">
        {{ auth()->user()->name }}
    </div>
</aside>

{{-- Main --}}
<div class="main">
    <div class="topbar">
        <span class="topbar-title">@yield('title', 'Dashboard')</span>
        <div class="topbar-user">
            {{ auth()->user()->user_id_number }}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </div>

    <div class="content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

</body>
</html>

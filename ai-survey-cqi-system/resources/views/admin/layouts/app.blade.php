<!DOCTYPE html>

<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>@yield('title', 'Dashboard') | CQI System</title>

{{-- Bootstrap Icons --}}
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"> --}}

{{-- Vite Assets --}}
@vite(['resources/sass/app.scss', 'resources/js/app.js'])

@stack('styles')
```

</head>

<body>

<div class="app-wrapper">

```
{{-- Sidebar --}}
<aside class="sidebar">

    <div class="sidebar-brand">
        CQI System
        <span>{{ auth()->user()->primaryRole() }}</span>
    </div>

    <nav class="sidebar-nav">

        {{-- Always visible --}}
        <a href="{{ route(auth()->user()->primaryRole() . '.dashboard') }}"
           class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        {{-- Role based navigation --}}
        @if(auth()->user()->hasRole('admin'))
            @include('partials.nav-admin')
        @endif

        @if(auth()->user()->hasRole('faculty'))
            @include('partials.nav-faculty')
        @endif

        @if(auth()->user()->hasRole('student'))
            @include('partials.nav-student')
        @endif

    </nav>

    <div class="sidebar-footer">

        <div class="sidebar-user">
            {{ auth()->user()->name }}
        </div>

        <div class="theme-switch">

            <i class="bi bi-sun-fill icon-sun"></i>

            <label class="switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>

            <i class="bi bi-moon-fill icon-moon"></i>

        </div>

    </div>

</aside>

{{-- Main Layout --}}
<main class="main">

    {{-- Topbar --}}
    <header class="topbar">

        <div>

            <h1 class="topbar-title">
                @yield('title', 'Dashboard')
            </h1>

            {{-- Breadcrumbs --}}
            @hasSection('breadcrumbs')
                <nav class="breadcrumbs">
                    @yield('breadcrumbs')
                </nav>
            @endif

        </div>

        <div class="topbar-user">

            <span class="user-id">
                {{ auth()->user()->user_id_number }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    Sign Out
                </button>
            </form>

        </div>

    </header>

    {{-- Page Content --}}
    <section class="content">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')

    </section>

</main>
```

</div>

</body>
</html>

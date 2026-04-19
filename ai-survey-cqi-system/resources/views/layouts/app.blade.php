<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/dcism_logo.ico') }}">

    <title>@yield('title', 'Dashboard') | CQI System</title>

    {{-- Vite: SCSS + JS --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>

<div class="app-wrapper {{ !auth()->check() ? 'guest-mode' : '' }}">

    {{-- ===================== SIDEBAR (Auth Only) ===================== --}}
    @auth
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <span class="brand-name">
                <i class="bi bi-mortarboard-fill me-2" style="color: var(--bs-blue)"></i>{{ setting('app.name') }}
            </span>
            <span class="brand-role">{{ auth()->user()?->primaryRole() }}</span>
        </div>

        <nav class="sidebar-nav">
            {{-- Dashboard (all roles) --}}
            <a href="{{ route(auth()->user()->primaryRole() . '.dashboard') }}"
               class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            {{-- Role-based nav partials --}}
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
            
            <span class="sidebar-user">
                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
            </span>
            <div class="theme-switch" title="Toggle dark mode">
                <label class="switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider">
                        <i class="bi bi-sun-fill icon sun"></i>
                        <i class="bi bi-moon-fill icon moon"></i>
                    </span>
                </label>
            </div>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    @endauth

    {{-- ===================== MAIN ===================== --}}
    <main class="main {{ !auth()->check() ? 'ms-0 w-100' : '' }}">

        {{-- Topbar (Auth Only) --}}
        @auth
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>

                <div class="topbar-title-group">
                    <h1 class="topbar-title">@yield('title', 'Dashboard')</h1>
                    @hasSection('breadcrumbs')
                        <nav class="breadcrumbs" aria-label="breadcrumb">
                            @yield('breadcrumbs')
                        </nav>
                    @endif
                </div>
            </div>

            <div class="topbar-right">
                <div class="dropdown">
                    <button class="topbar-user-btn dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="topbar-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="d-none d-sm-block text-start">
                            <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                            <div class="topbar-user-id">{{ auth()->user()->user_id_number }}</div>
                        </div>
                        <i class="bi bi-chevron-down" style="font-size:.65rem; color: var(--bs-secondary-color)"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <span class="dropdown-item-text small text-muted">
                                {{ auth()->user()->user_id_number }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        @endauth

        {{-- ===================== CONTENT ===================== --}}
        <section class="content {{ !auth()->check() ? 'pt-4' : '' }}">
            {{-- Flash alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </section>

    </main>

</div>

{{-- Scripts stay the same --}}
<script>
(function () {
    const THEME_KEY = 'cqi-theme';
    const html = document.documentElement;
    const toggle = document.getElementById('themeToggle');

    const saved = localStorage.getItem(THEME_KEY) || 'light';
    html.setAttribute('data-bs-theme', saved);
    if (toggle) toggle.checked = (saved === 'dark');

    if (toggle) {
        toggle.addEventListener('change', function () {
            const next = this.checked ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem(THEME_KEY, next);
        });
    }

    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const burger = document.getElementById('sidebarToggle');

    function openSidebar() { if(sidebar) sidebar.classList.add('show'); if(overlay) overlay.classList.add('show'); }
    function closeSidebar() { if(sidebar) sidebar.classList.remove('show'); if(overlay) overlay.classList.remove('show'); }

    if (burger) burger.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
})();
</script>

@stack('scripts')

</body>
</html>
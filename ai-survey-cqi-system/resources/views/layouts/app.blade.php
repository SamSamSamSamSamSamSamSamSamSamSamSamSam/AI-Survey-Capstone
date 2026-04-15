<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/dcism_logo.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <title>@yield('title', 'Dashboard') | CQI System</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>

{{-- ===================== APP SHELL ===================== --}}
<div class="app-shell">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside class="sidebar" id="appSidebar">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="sidebar-brand-text">
                <span class="brand-name">CQI System</span>
                <span class="brand-sub">Quality Improvement</span>
            </div>
            {{-- Desktop collapse button --}}
            <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Collapse sidebar">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        {{-- Role badge --}}
        <div class="sidebar-role-badge">
            @php
                $role = auth()->user()?->primaryRole();
                $roleClass = match($role) {
                    'admin'   => 'role-admin',
                    'faculty' => 'role-faculty',
                    'student' => 'role-student',
                    default   => 'role-default',
                };
            @endphp
            <span class="role-chip {{ $roleClass }}">
                <i class="bi bi-{{ $role === 'admin' ? 'shield-fill' : ($role === 'faculty' ? 'person-workspace' : 'mortarboard') }}"></i>
                <span class="role-label">{{ ucfirst($role) }}</span>
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav" aria-label="Main navigation">

            <div class="nav-section">
                <span class="nav-section-label">Main</span>

                <a href="{{ route(auth()->user()->primaryRole() . '.dashboard') }}"
                   class="nav-item {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                    <span class="nav-label">Dashboard</span>
                    @if(request()->routeIs('*.dashboard'))
                        <span class="nav-active-bar"></span>
                    @endif
                </a>
            </div>

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

        {{-- Sidebar Footer --}}
        <div class="sidebar-footer">
            <div class="sidebar-user-info">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="sidebar-user-details">
                    <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                    <span class="sidebar-user-id">{{ auth()->user()->user_id_number }}</span>
                </div>
            </div>

            <div class="sidebar-footer-actions">
                <div class="theme-toggle-wrap" title="Toggle dark mode">
                    <label class="theme-toggle" aria-label="Toggle dark mode">
                        <input type="checkbox" id="themeToggle">
                        <span class="theme-toggle-track">
                            <i class="bi bi-sun-fill toggle-icon sun-icon"></i>
                            <i class="bi bi-moon-fill toggle-icon moon-icon"></i>
                            <span class="theme-toggle-thumb"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

    </aside>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    {{-- ===================== MAIN AREA ===================== --}}
    <div class="main-area">

        {{-- Topbar --}}
        <header class="topbar" role="banner">

            <div class="topbar-start">
                {{-- Mobile hamburger --}}
                <button class="topbar-hamburger" id="sidebarToggle" aria-label="Open menu" aria-expanded="false">
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                </button>

                {{-- Page title + breadcrumb --}}
                <div class="topbar-heading">
                    <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                    @hasSection('breadcrumbs')
                        <nav class="page-breadcrumb" aria-label="breadcrumb">
                            @yield('breadcrumbs')
                        </nav>
                    @endif
                </div>
            </div>

            <div class="topbar-end">

                {{-- Notification bell (placeholder) --}}
                <button class="topbar-icon-btn" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notif-dot" aria-hidden="true"></span>
                </button>

                {{-- User dropdown --}}
                <div class="dropdown">
                    <button class="topbar-user-btn dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                        <div class="topbar-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="topbar-user-meta d-none d-sm-block">
                            <span class="topbar-user-name">{{ auth()->user()->name }}</span>
                            <span class="topbar-user-id">{{ auth()->user()->user_id_number }}</span>
                        </div>
                        <i class="bi bi-chevron-down topbar-chevron"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end topbar-dropdown-menu">
                        <li class="dropdown-header-item">
                            <div class="dropdown-user-preview">
                                <div class="dropdown-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="dropdown-user-name">{{ auth()->user()->name }}</div>
                                    <div class="dropdown-user-id">{{ auth()->user()->user_id_number }}</div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-signout">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </header>

        {{-- ===================== CONTENT ===================== --}}
        <main class="content-area" id="mainContent" role="main">

            {{-- Flash alerts --}}
            @if(session('success'))
                <div class="alert-flash alert-flash-success alert-dismissible fade show" role="alert">
                    <div class="alert-flash-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="alert-flash-body">{{ session('success') }}</div>
                    <button type="button" class="alert-flash-close btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-flash alert-flash-danger alert-dismissible fade show" role="alert">
                    <div class="alert-flash-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="alert-flash-body">{{ session('error') }}</div>
                    <button type="button" class="alert-flash-close btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert-flash alert-flash-info alert-dismissible fade show" role="alert">
                    <div class="alert-flash-icon"><i class="bi bi-info-circle-fill"></i></div>
                    <div class="alert-flash-body">{{ session('info') }}</div>
                    <button type="button" class="alert-flash-close btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Page content --}}
            @yield('content')

        </main>

    </div>{{-- /.main-area --}}

</div>{{-- /.app-shell --}}

{{-- ===================== SCRIPTS ===================== --}}
<script>
(function () {
    'use strict';

    /* ── Theme persistence ── */
    const THEME_KEY  = 'cqi-theme';
    const SIDEBAR_KEY = 'cqi-sidebar-collapsed';
    const html       = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');

    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    if (themeToggle) themeToggle.checked = (savedTheme === 'dark');

    if (themeToggle) {
        themeToggle.addEventListener('change', function () {
            const next = this.checked ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem(THEME_KEY, next);
        });
    }

    /* ── Desktop sidebar collapse ── */
    const sidebar     = document.getElementById('appSidebar');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const appShell    = document.querySelector('.app-shell');

    const isCollapsed = localStorage.getItem(SIDEBAR_KEY) === 'true';
    if (isCollapsed && appShell) appShell.classList.add('sidebar-collapsed');

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            const collapsed = appShell.classList.toggle('sidebar-collapsed');
            localStorage.setItem(SIDEBAR_KEY, collapsed);
        });
    }

    /* ── Mobile sidebar ── */
    const overlay    = document.getElementById('sidebarOverlay');
    const hamburger  = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar?.classList.add('sidebar-open');
        overlay?.classList.add('overlay-visible');
        hamburger?.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sidebar-mobile-open');
    }

    function closeSidebar() {
        sidebar?.classList.remove('sidebar-open');
        overlay?.classList.remove('overlay-visible');
        hamburger?.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-mobile-open');
    }

    hamburger?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    /* ── Keyboard: Esc closes mobile sidebar ── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    /* ── Auto-dismiss flash alerts after 5s ── */
    setTimeout(function () {
        document.querySelectorAll('.alert-flash').forEach(function (el) {
            el.classList.remove('show');
            setTimeout(function () { el.remove(); }, 300);
        });
    }, 5000);

})();
</script>

@stack('scripts')

</body>
</html>
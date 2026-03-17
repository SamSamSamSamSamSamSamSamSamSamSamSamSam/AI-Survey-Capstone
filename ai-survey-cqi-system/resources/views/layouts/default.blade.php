<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DCISM Admin Portal</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="layout-wrapper">

        {{-- ======================================================
             SIDEBAR
        ====================================================== --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-inner">

                {{-- Brand --}}
                <div class="sidebar-brand-wrap">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                        <img src="{{ asset('images/dcismicon.png') }}" alt="DCISM Icon" class="sidebar-brand-logo">
                        <span class="sidebar-brand-text">DCISM</span>
                    </a>
                </div>

                {{-- User Info --}}
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="sidebar-user-details">
                        <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                        <div class="sidebar-user-role">
                            @if(Auth::user()->hasRole('admin'))
                                <span class="role-badge role-badge--admin">Administrator</span>
                            @elseif(Auth::user()->hasRole('teacher'))
                                <span class="role-badge role-badge--teacher">Faculty</span>
                            @else
                                <span class="role-badge role-badge--student">Student</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Navigation — role-specific nav items live in their own partials --}}
                <nav class="sidebar-nav">
                    <ul class="sidebar-menu" id="sidebarMenu">
                        @if(Auth::user()->hasRole('admin'))
                            @include('partials.nav-admin')
                        @elseif(Auth::user()->hasRole('teacher'))
                            @include('partials.nav-teacher')
                        @else
                            @include('partials.nav-student')
                        @endif
                    </ul>
                </nav>

                {{-- Logout --}}
                <div class="sidebar-footer">
                    <a href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="sidebar-logout-btn">
                        <i class="bi bi-box-arrow-right sidebar-menu-icon"></i>
                        <span class="sidebar-menu-label">Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>

            </div>
        </aside>

        {{-- ======================================================
             MAIN AREA
        ====================================================== --}}
        <div class="main-wrapper">

            {{-- Mobile topbar --}}
            <header class="topbar d-md-none">
                <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <span class="topbar-title">DCISM Portal</span>
            </header>

            {{-- Page content --}}
            <main class="page-content">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="site-footer">
                <p class="mb-0">&copy; {{ date('Y') }} DCISM Admin Portal. All rights reserved.</p>
            </footer>

        </div>
    </div>

    @stack('scripts')

    <script>
        (() => {
            const sidebar   = document.getElementById('sidebar');
            const overlay   = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');

            const openSidebar  = () => { sidebar.classList.add('is-open');    overlay.classList.add('is-visible'); };
            const closeSidebar = () => { sidebar.classList.remove('is-open'); overlay.classList.remove('is-visible'); };

            toggleBtn?.addEventListener('click', () => {
                sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
            });

            overlay?.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
        })();
    </script>

</body>
</html>
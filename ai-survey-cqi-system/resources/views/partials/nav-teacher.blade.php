<li class="sidebar-menu-item">
    <a href="{{ route('teacher.dashboard') }}"
       class="sidebar-menu-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}">
        <i class="bi bi-journal-text sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Dashboard</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('teacher.survey') }}"
       class="sidebar-menu-link {{ request()->routeIs('teacher.survey') ? 'is-active' : '' }}">
        <i class="bi bi-clipboard-check sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Survey</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('teacher.reviews') }}"
       class="sidebar-menu-link {{ request()->routeIs('teacher.reviews') ? 'is-active' : '' }}">
        <i class="bi bi-bar-chart-line sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Results</span>
    </a>
</li>
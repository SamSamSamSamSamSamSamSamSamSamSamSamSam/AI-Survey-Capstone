<li class="sidebar-menu-item">
    <a href="{{ route('student.dashboard') }}"
       class="sidebar-menu-link {{ request()->routeIs('student.dashboard') ? 'is-active' : '' }}">
        <i class="bi bi-house sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Dashboard</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('student.survey') }}"
       class="sidebar-menu-link {{ request()->routeIs('student.survey') ? 'is-active' : '' }}">
        <i class="bi bi-clipboard-check sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Surveys</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('student.results') }}"
       class="sidebar-menu-link {{ request()->routeIs('student.results') ? 'is-active' : '' }}">
        <i class="bi bi-bar-chart-line sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Results</span>
    </a>
</li>
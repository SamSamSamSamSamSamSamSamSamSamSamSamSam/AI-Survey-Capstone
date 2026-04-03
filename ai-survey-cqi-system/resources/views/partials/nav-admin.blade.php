<li class="sidebar-menu-item">
    <a href="{{ route('admin.dashboard') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Dashboard</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.semesters.index') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.semesters.*') ? 'is-active' : '' }}">
        <i class="bi bi-calendar2-range sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Semesters</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.department') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.department') ? 'is-active' : '' }}">
        <i class="bi bi-building sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Department</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.users') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.users') ? 'is-active' : '' }}">
        <i class="bi bi-people sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Users</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.settings.index') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
        <i class="bi bi-gear sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Settings</span>
    </a>
</li>
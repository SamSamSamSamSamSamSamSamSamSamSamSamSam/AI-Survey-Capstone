{{-- Dashboard --}}
<li class="sidebar-menu-item">
    <a href="{{ route('admin.dashboard') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Dashboard</span>
    </a>
</li>

{{-- Survey Management --}}
<li class="sidebar-menu-section">Survey Management</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.surveys.index') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.surveys.*') ? 'is-active' : '' }}">
        <i class="bi bi-clipboard2-check sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Surveys</span>
    </a>
</li>

{{-- Analytics --}}
<li class="sidebar-menu-section">Analytics</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.analysis.surveys') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.analysis.surveys') ? 'is-active' : '' }}">
        <i class="bi bi-bar-chart-line sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Survey Analysis</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.analysis.questionAnalysis') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.analysis.questionAnalysis') ? 'is-active' : '' }}">
        <i class="bi bi-patch-question sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Question Analysis</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.analysis.wordCloud') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.analysis.wordCloud') ? 'is-active' : '' }}">
        <i class="bi bi-chat-square-text sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Word Cloud</span>
    </a>
</li>

{{-- CQI Reports --}}
<li class="sidebar-menu-section">CQI Reports</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.reports.filter') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}">
        <i class="bi bi-file-earmark-bar-graph sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Generate CQI Report</span>
    </a>
</li>

{{-- Academic Management --}}
<li class="sidebar-menu-section">Academic Management</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.users') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
        <i class="bi bi-people sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Users</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.department') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.department') ? 'is-active' : '' }}">
        <i class="bi bi-building sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Departments</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.subjects.index') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.subjects.*') ? 'is-active' : '' }}">
        <i class="bi bi-book sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Subjects</span>
    </a>
</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.semesters.index') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.semesters.*') ? 'is-active' : '' }}">
        <i class="bi bi-calendar2-range sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Semesters</span>
    </a>
</li>

{{-- System --}}
<li class="sidebar-menu-section">System</li>

<li class="sidebar-menu-item">
    <a href="{{ route('admin.settings.index') }}"
       class="sidebar-menu-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
        <i class="bi bi-gear sidebar-menu-icon"></i>
        <span class="sidebar-menu-label">Settings</span>
    </a>
</li>
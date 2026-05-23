<p class="nav-section">My Workspace</p>

<a href="{{ route('faculty.reports.index') }}" class="nav-link {{ request()->routeIs('faculty.reports.*') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> <span>Performance Reports</span>
</a>

<a href="{{ route('faculty.analytics.charts') }}" class="nav-link {{ request()->routeIs('faculty.analytics.charts') ? 'active' : '' }}">
    <i class="bi bi-bar-chart"></i> <span>Analytics Dashboard</span>
</a>

<a href="{{ route('survey.index') }}" class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i> <span>Assigned Surveys</span>
</a>
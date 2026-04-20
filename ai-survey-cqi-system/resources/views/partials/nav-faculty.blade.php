<p class="nav-section">Main</p>

<a href="{{ route('survey.index') }}"
   class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i>
    <span>My Surveys</span>
</a>

<a href="{{ route('faculty.reports.index') }}"
   class="nav-link {{ request()->routeIs('faculty.reports.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    <span>My Reports</span>
</a>

<a href="{{ route('faculty.analytics.charts') }}"
   class="nav-link {{ request()->routeIs('faculty.analytics.charts') ? 'active' : '' }}">
    <i class="bi bi-clipboard-data"></i>
    <span>My Analytics</span>
</a>
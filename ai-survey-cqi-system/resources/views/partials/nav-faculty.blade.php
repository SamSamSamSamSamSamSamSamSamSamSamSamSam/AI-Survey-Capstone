<p class="nav-section">Surveys</p>

<a href="{{ route('survey.index') }}"
   class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    <span>My Surveys</span>
</a>

<p class="nav-section">Reports</p>

<a href="{{ route('faculty.reports.index') }}"
   class="nav-link {{ request()->routeIs('faculty.reports.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    <span>My Reports</span>
</a>
<p class="nav-section">Surveys</p>

<a href="{{ route('survey.index') }}"
   class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i>
    <span>My Surveys</span>
</a>

<p class="nav-section">Enrollments</p>

<a href="{{ route('student.enrollments.index') }}"
   class="nav-link {{ request()->routeIs('student.enrollments.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    <span>My Enrollments</span>
</a>
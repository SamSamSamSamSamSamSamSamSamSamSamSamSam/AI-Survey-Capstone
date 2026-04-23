<p class="nav-section">Student Hub</p>

<a href="{{ route('survey.index') }}" 
   class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i> 
    <span>Available Surveys</span>
</a>

<a href="{{ route('student.enrollments.index') }}" 
   class="nav-link {{ request()->routeIs('student.enrollments.*') ? 'active' : '' }}">
    <i class="bi bi-book"></i> 
    <span>Enrolled Subjects</span>
</a>
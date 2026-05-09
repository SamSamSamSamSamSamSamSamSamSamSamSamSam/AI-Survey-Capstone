<p class="nav-section">Main</p>

<a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> <span>Users</span>
</a>

<a href="{{ route('admin.surveys.index') }}" 
   class="nav-link {{ (request()->routeIs('admin.surveys.*') && !request()->routeIs('admin.surveys.global-assign*')) ? 'active' : '' }}">
    <i class="bi bi-ui-checks-grid"></i> <span>Survey Manager</span>
</a>

<a href="{{ route('admin.semester-setup.index') }}" class="nav-link {{ request()->routeIs('admin.semester-setup.*') ? 'active' : '' }}">
   <i class="bi bi-calendar2-plus"></i> <span>Semester Setup</span>
</a>

<a href="{{ route('admin.offerings.index') }}" class="nav-link {{ request()->routeIs('admin.offerings.*') ? 'active' : '' }}">
    <i class="bi bi-easel"></i> <span>Class Offerings</span>
</a>

<p class="nav-section">Analytics &amp; CQI</p>

<a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart"></i> <span>Faculty Analytics</span>
</a>

<a href="{{ route('admin.cqi-reports.index') }}" class="nav-link {{ request()->routeIs('admin.cqi-reports.*') ? 'active' : '' }}">
    <i class="bi bi-clipboard-data"></i> <span>CQI Reports</span>
</a>

<p class="nav-section">Survey Management</p>

<a href="{{ route('admin.surveys.global-assign') }}" class="nav-link {{ request()->routeIs('admin.surveys.global-assign*') ? 'active' : '' }}">
   <i class="bi bi-send-check"></i> <span>Deploy Surveys</span>
</a>

<a href="{{ route('admin.survey-templates.index') }}" class="nav-link {{ request()->routeIs('admin.survey-templates.*') ? 'active' : '' }}">
    <i class="bi bi-layout-text-sidebar"></i> <span>Templates</span>
</a>

<a href="{{ route('admin.question-categories.index') }}" class="nav-link {{ request()->routeIs('admin.question-categories.*') ? 'active' : '' }}">
    <i class="bi bi-tags"></i> <span>Categories</span>
</a>

<a href="{{ route('survey.index') }}" class="nav-link {{ request()->routeIs('survey.*') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i> <span>My Surveys</span>
</a>

<p class="nav-section">Academic Structure</p>

<a href="{{ route('admin.programs.index') }}" class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
    <i class="bi bi-mortarboard"></i> <span>Programs</span>
</a>

<a href="{{ route('admin.curricula.index') }}" class="nav-link {{ request()->routeIs('admin.curricula.*') ? 'active' : '' }}">
    <i class="bi bi-journal-text"></i> <span>Curricula</span>
</a>

<a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
    <i class="bi bi-book"></i> <span>Subjects</span>
</a>

<a href="{{ route('admin.semesters.index') }}" class="nav-link {{ request()->routeIs('admin.semesters.*') ? 'active' : '' }}">
    <i class="bi bi-calendar3"></i> <span>Academic Terms</span>
</a>

<a href="{{ route('admin.prospectus.index') }}" class="nav-link {{ request()->routeIs('admin.prospectus.*') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> <span>Prospectus</span>
</a>

{{-- <p class="nav-section">System</p>

<a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
   <i class="bi bi-gear"></i> <span>Settings</span>
</a> --}}
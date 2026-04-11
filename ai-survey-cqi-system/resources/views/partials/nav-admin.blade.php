<p class="nav-section">Users</p>

<a href="{{ route('admin.users.index') }}"
class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"> <i class="bi bi-people"></i>
User Management </a>

<p class="nav-section">Surveys</p>

<a href="{{ route('admin.surveys.index') }}"
class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}"> <i class="bi bi-ui-checks-grid"></i>
Surveys </a>

<a href="{{ route('admin.survey-templates.index') }}"
class="nav-link {{ request()->routeIs('admin.survey-templates.*') ? 'active' : '' }}"> <i class="bi bi-layout-text-sidebar"></i>
Templates </a>

<a href="{{ route('admin.question-categories.index') }}"
class="nav-link {{ request()->routeIs('admin.question-categories.*') ? 'active' : '' }}"> <i class="bi bi-tags"></i>
Categories </a>

<p class="nav-section">Academic Structure</p>

<a href="{{ route('admin.programs.index') }}"
class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}"> <i class="bi bi-mortarboard"></i>
Programs </a>

<a href="{{ route('admin.curricula.index') }}"
class="nav-link {{ request()->routeIs('admin.curricula.*') ? 'active' : '' }}"> <i class="bi bi-journal-text"></i>
Curricula </a>

<a href="{{ route('admin.subjects.index') }}"
class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"> <i class="bi bi-book"></i>
Subjects </a>

<a href="{{ route('admin.semesters.index') }}"
class="nav-link {{ request()->routeIs('admin.semesters.*') ? 'active' : '' }}"> <i class="bi bi-calendar3"></i>
Semesters </a>

<a href="{{ route('admin.prospectus.index') }}"
class="nav-link {{ request()->routeIs('admin.prospectus.*') ? 'active' : '' }}"> <i class="bi bi-file-earmark-text"></i>
Prospectus </a>

<a href="{{ route('admin.offerings.index') }}"
class="nav-link {{ request()->routeIs('admin.offerings.*') ? 'active' : '' }}"> <i class="bi bi-easel"></i>
Course Offerings </a>

<p class="nav-section">Analytics & CQI</p>

<a href="{{ route('admin.analytics.index') }}"
class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}"> <i class="bi bi-bar-chart"></i>
Faculty Analytics </a>

<a href="{{ route('admin.cqi-reports.index') }}"
class="nav-link {{ request()->routeIs('admin.cqi-reports.*') ? 'active' : '' }}"> <i class="bi bi-clipboard-data"></i>
CQI Reports </a>

{{--
    UPDATE resources/views/admin/layouts/app.blade.php
    Replace the Surveys nav section with this expanded version:
--}}

<p class="nav-section">Surveys</p>
<a href="{{ route('admin.surveys.index') }}"
   class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
    Surveys
</a>
<a href="{{ route('admin.survey-templates.index') }}"
   class="nav-link {{ request()->routeIs('admin.survey-templates.*') ? 'active' : '' }}">
    Templates
</a>
<a href="{{ route('admin.question-categories.index') }}"
   class="nav-link {{ request()->routeIs('admin.question-categories.*') ? 'active' : '' }}">
    Categories
</a>

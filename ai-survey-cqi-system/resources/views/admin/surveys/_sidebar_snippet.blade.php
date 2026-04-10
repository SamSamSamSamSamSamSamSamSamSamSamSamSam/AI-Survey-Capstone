{{--
    ADD this nav section inside resources/views/admin/layouts/app.blade.php
    Place it after the Academic nav section.
--}}

<p class="nav-section">Surveys</p>
<a href="{{ route('admin.surveys.index') }}"
   class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
    Surveys
</a>

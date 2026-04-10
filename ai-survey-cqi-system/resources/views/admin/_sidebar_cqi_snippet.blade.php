{{--
    UPDATE resources/views/admin/layouts/app.blade.php
    Add this section after the Surveys nav section:
--}}

<p class="nav-section">Analytics & CQI</p>
<a href="{{ route('admin.analytics.index') }}"
   class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
    Faculty Analytics
</a>
<a href="{{ route('admin.cqi-reports.index') }}"
   class="nav-link {{ request()->routeIs('admin.cqi-reports.*') ? 'active' : '' }}">
    CQI Reports
</a>

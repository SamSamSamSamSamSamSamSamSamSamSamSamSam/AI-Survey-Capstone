@props(['activeLink' => null, 'menuItems' => []])
@php
    use Illuminate\Support\Facades\Route;
    $user = $user ?? auth()->user();
    $role = strtolower($role ?? $user->role ?? 'guest');

    if (! function_exists('safe_route')) {
        function safe_route($name, $fallback = '#') {
            if (empty($name)) return $fallback;
            if (Route::has($name)) return route($name);
            if (Route::has($name . '.index')) return route($name . '.index');
            return $fallback;
        }
    }
@endphp

<div class="card sidebar-card p-3">
	<div class="mb-3">
		<div class="h6 mb-0">{{ $user->name ?? 'Guest' }}</div>
		<div class="small text-muted">{{ ucfirst($role) }}</div>
	</div>

	<div class="list-group">
		<a href="{{ $role === 'admin' ? safe_route('admin.dashboard', url('/')) : safe_route('dashboard', url('/')) }}" class="list-group-item list-group-item-action">
			<i class="fa-solid fa-gauge me-2"></i> Dashboard
		</a>

		@if($role === 'admin')
			<a href="{{ safe_route('admin.users.index') }}" class="list-group-item list-group-item-action"> <i class="fa-solid fa-users me-2"></i> Manage Users</a>
			<a href="{{ safe_route('admin.departments.index') }}" class="list-group-item list-group-item-action"> <i class="fa-solid fa-building me-2"></i> Departments</a>
			<a href="{{ safe_route('admin.reports.index') }}" class="list-group-item list-group-item-action"> <i class="fa-solid fa-chart-simple me-2"></i> Reports</a>
			<a href="{{ safe_route('admin.settings.index') }}" class="list-group-item list-group-item-action"> <i class="fa-solid fa-gears me-2"></i> Settings</a>
		@elseif($role === 'teacher')
			<a href="{{ safe_route('teacher.dashboard') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-chalkboard-user me-2"></i> My Dashboard</a>
			<a href="{{ safe_route('teacher.classes') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-book-open me-2"></i> Classes</a>
			<a href="{{ safe_route('teacher.reviews') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-comments me-2"></i> Feedback</a>
		@elseif($role === 'student')
			<a href="{{ safe_route('student.dashboard') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-file-lines me-2"></i> My Surveys</a>
			<a href="{{ safe_route('student.surveys') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-list-check me-2"></i> Surveys</a>
			<a href="{{ safe_route('student.results') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-chart-column me-2"></i> Results</a>
		@else
			<a href="{{ url('/') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-house me-2"></i> Home</a>
			<a href="{{ safe_route('login') }}" class="list-group-item list-group-item-action"><i class="fa-solid fa-right-to-bracket me-2"></i> Sign In</a>
		@endif

		<form method="POST" action="{{ safe_route('logout', url('/logout')) }}">
			@csrf
			<button type="submit" class="list-group-item list-group-item-action text-start btn btn-link p-0 mt-2"> <i class="fa-solid fa-right-from-bracket me-2"></i> Logout</button>
		</form>
	</div>
</div>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>@yield('title','Admin') - CQI System</title>

	{{-- Bootstrap + Font Awesome --}}
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />

	<style>
		/* filepath: resources\views\layouts\admin.blade.php - custom styles */
		body { background:#f5f7fb; }
		.sidebar-card { min-height: 100vh; border-radius:.5rem; box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
		.nav-link-custom { color:#fff; }
		.nav-link-custom:hover { color:#fff; text-decoration:none; }
		.main-card { border-radius:.5rem; box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
		.small-muted { color:#6b7280; }
	</style>

	@yield('header')
</head>
<body>
@php
	use Illuminate\Support\Facades\Route;
	if (! function_exists('safe_route')) {
		function safe_route($name, $fallback = '#') {
			if (empty($name)) return $fallback;
			if (Route::has($name)) return route($name);
			if (Route::has($name . '.index')) return route($name . '.index');
			return $fallback;
		}
	}
@endphp

<div class="container-fluid">
	<div class="row g-0">
		{{-- Left column placeholder — many views include <x-sidebar/> directly; keep layout generic --}}
		<aside class="col-12 col-md-4 col-lg-3 p-3">
			{{-- If a view uses its own sidebar, it will render there; otherwise we can show a compact placeholder --}}
			@hasSection('sidebar')
				@yield('sidebar')
			@else
				{{-- fallback small sidebar card to keep layout consistent --}}
				<div class="card sidebar-card bg-primary text-white p-3">
					<div class="mb-3">
						<div class="h5 mb-0">{{ auth()->user()->name ?? 'Guest' }}</div>
						<div class="small text-white-50">{{ ucfirst(auth()->user()->role ?? 'guest') }}</div>
					</div>
					<nav class="nav flex-column">
						<a class="nav-link nav-link-custom py-2" href="{{ safe_route('admin.dashboard', url('/')) }}"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
						<a class="nav-link nav-link-custom py-2" href="{{ safe_route('admin.users.index','#') }}"><i class="fa-solid fa-users me-2"></i> Users</a>
						<a class="nav-link nav-link-custom py-2" href="{{ safe_route('admin.departments.index','#') }}"><i class="fa-solid fa-building me-2"></i> Departments</a>
						<a class="nav-link nav-link-custom py-2" href="{{ safe_route('admin.reports.index','#') }}"><i class="fa-solid fa-chart-simple me-2"></i> Reports</a>
						<a class="nav-link nav-link-custom py-2" href="{{ safe_route('admin.settings.index','#') }}"><i class="fa-solid fa-gears me-2"></i> Settings</a>
						<form method="POST" action="{{ safe_route('logout', url('/logout')) }}">
							@csrf
							<button class="btn btn-link text-white ps-0 pt-2" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</button>
						</form>
					</nav>
				</div>
			@endif
		</aside>

		{{-- Main content area --}}
		<main class="col-12 col-md-8 col-lg-9 p-4">
			<header class="mb-3">
				@yield('header')
			</header>

			<section class="mb-4">
				@yield('content')
			</section>

			<footer class="mt-4">
				@yield('footer')
			</footer>
		</main>
	</div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
@yield('scripts')
</body>
</html>
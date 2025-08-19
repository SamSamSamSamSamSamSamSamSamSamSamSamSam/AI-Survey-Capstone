@extends('layouts.admin') 

@section('header')
	{{-- Add Bootstrap + FontAwesome and a small custom stylesheet (will be applied even if layout doesn't include head stacks) --}}
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="" crossorigin="anonymous" />

	<style>
		/* filepath: c:\Users\Arjoy\Desktop\DESKTOP\Projects\Capstoneproject\student_cqi_system\laravel-front\resources\views\admin\dashboard.blade.php - custom styles */
		.page-title { font-size: 1.5rem; font-weight: 600; }
		.card-compact { box-shadow: 0 6px 18px rgba(15,23,42,0.06); border-radius: .5rem; }
		.sidebar-placeholder { min-height: 80vh; }
		.stat-badge { font-size: .9rem; padding:.25rem .5rem; border-radius:.375rem; }
		.comment-card { background:#fff; border-radius:.5rem; padding:1rem; box-shadow: 0 4px 12px rgba(15,23,42,0.04); }
	</style>

	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="page-title mb-0">Dashboard</h1>
		{{-- small controls placeholder (filters will be wired later) --}}
		<div>
			<select id="filter-department" class="form-select form-select-sm d-inline-block" style="width:auto;">
				<option value="">All Departments</option>
				{{-- future: backend should populate department options --}}
			</select>
			<select id="filter-term" class="form-select form-select-sm d-inline-block ms-2" style="width:auto;">
				<option value="">All Terms</option>
			</select>
		</div>
	</div>
@endsection

@section('content')
	<div class="container-fluid">
		<div class="row">
			{{-- Sidebar (left) --}}
			<div class="col-12 col-md-4 col-lg-3 mb-4 sidebar-placeholder">
				<x-sidebar />
			</div>

			{{-- Main content (right) --}}
			<div class="col-12 col-md-8 col-lg-9">
				<div class="mb-4">
					<h2 class="h4">Welcome, {{ auth()->user()->name ?? 'Admin' }}!</h2>
					<p class="text-muted mb-0">Role: <strong>{{ auth()->user()->role ?? 'N/A' }}</strong></p>
				</div>

				{{-- Top summary cards --}}
				<div class="row g-3 mb-4">
					<div class="col-12 col-sm-6 col-lg-3">
						<div class="card card-compact p-3">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<div class="text-muted small">Total Surveys</div>
									<div class="h5 mb-0" id="stat-total-surveys">--</div>
								</div>
								<div class="text-primary">
									<i class="fa-solid fa-chart-line fa-2x"></i>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-6 col-lg-3">
						<div class="card card-compact p-3">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<div class="text-muted small">Average Score</div>
									<div class="h5 mb-0" id="stat-average-score">--</div>
								</div>
								<div class="text-success">
									<i class="fa-solid fa-star fa-2x"></i>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-6 col-lg-3">
						<div class="card card-compact p-3">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<div class="text-muted small">Top Faculty</div>
									<div class="h6 mb-0" id="stat-top-faculty">--</div>
								</div>
								<div class="text-warning">
									<i class="fa-solid fa-award fa-2x"></i>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-6 col-lg-3">
						<div class="card card-compact p-3">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<div class="text-muted small">Departments</div>
									<div class="h5 mb-0" id="stat-departments">--</div>
								</div>
								<div class="text-info">
									<i class="fa-solid fa-building fa-2x"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				{{-- Main panels --}}
				<div class="row gy-4">
					<div class="col-12 col-lg-8">
						<div class="card p-3 card-compact">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<h5 class="mb-0">Department Performance</h5>
								<small class="text-muted">Monthly average scores</small>
							</div>
							<canvas id="deptPerformanceChart" height="160" class="w-100"></canvas>
							<small class="text-muted mt-2 d-block">Hover to inspect points. Data is loaded from backend when available.</small>
						</div>
					</div>

					<div class="col-12 col-lg-4">
						<div class="card p-3 card-compact mb-3">
							<h5 class="mb-3">Top Performing Faculty</h5>
							<ul class="list-group list-group-flush" id="top-performers-list">
								@php
									$topFaculty = $topFaculty ?? [
										['name'=>'Dr. Alice', 'score'=>4.9],
										['name'=>'Prof. Bob', 'score'=>4.8],
										['name'=>'Dr. Carol', 'score'=>4.7],
									];
								@endphp
								@foreach($topFaculty as $f)
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<div>
											<div class="fw-semibold">{{ $f['name'] }}</div>
											<div class="small text-muted">Score: {{ $f['score'] }}</div>
										</div>
										<span class="badge bg-success">{{ $f['score'] }}</span>
									</li>
								@endforeach
							</ul>
						</div>

						<div class="card p-3 card-compact">
							<h5 class="mb-3">Faculty Status</h5>
							<ul class="list-group list-group-flush" id="faculty-status-list">
								@php
									$facultyStats = $facultyStats ?? [
										['name'=>'Dr. Alice','status'=>'Active','score'=>4.9],
										['name'=>'Prof. Bob','status'=>'On Leave','score'=>4.8],
										['name'=>'Dr. Carol','status'=>'Active','score'=>4.7],
									];
								@endphp
								@foreach($facultyStats as $fs)
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<div>
											<div class="fw-semibold">{{ $fs['name'] }}</div>
											<div class="small text-muted">{{ $fs['status'] }}</div>
										</div>
										<div class="small text-muted">{{ $fs['score'] }}</div>
									</li>
								@endforeach
							</ul>
						</div>
					</div>

					{{-- Highlighted comments --}}
					<div class="col-12">
						<div class="card p-3 card-compact">
							<h5 class="mb-3">Highlighted Comments</h5>
							<div id="comments-list" class="row g-3">
								@php
									$comments = $comments ?? [
										['author'=>'Student A','text'=>'Great clarity and helpful feedback.'],
										['author'=>'Student B','text'=>'Needs to improve timeliness of grading.'],
										['author'=>'Student C','text'=>'Very engaging lectures.'],
									];
								@endphp
								@foreach($comments as $c)
									<div class="col-12 col-md-4">
										<div class="comment-card">
											<div class="small text-muted">By {{ $c['author'] }}</div>
											<div class="mt-2">{{ $c['text'] }}</div>
										</div>
									</div>
								@endforeach
							</div>
						</div>
					</div>
				</div>

				{{-- Additional widgets placeholder --}}
				<div class="mt-4">
					{{-- future widgets can be added here (filters, exports, advanced charts) --}}
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer')
	<footer class="mt-4 text-center small text-muted">
		&copy; {{ date('Y') }} CQI System.
	</footer>
@endsection

@section('scripts')
	{{-- Chart.js + Bootstrap JS --}}
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>

	{{-- Expose server data to frontend for future dynamic updates --}}
	<script>
		window.__dashboardData = {
			departmentData: {!! json_encode($departmentData ?? ['Jan'=>3.8,'Feb'=>4.0,'Mar'=>4.1,'Apr'=>4.0,'May'=>4.2]) !!},
			topFaculty: {!! json_encode($topFaculty ?? [['name'=>'Dr. Alice','score'=>4.9]]) !!},
			comments: {!! json_encode($comments ?? [['author'=>'Student A','text'=>'Great clarity and helpful feedback.']]) !!},
			facultyStats: {!! json_encode($facultyStats ?? [['name'=>'Dr. Alice','status'=>'Active','score'=>4.9]]) !!}
		};

		// Render Chart
		(function(){
			const labels = Object.keys(window.__dashboardData.departmentData);
			const values = Object.values(window.__dashboardData.departmentData);

			const ctx = document.getElementById('deptPerformanceChart');
			if (ctx) {
				const chart = new Chart(ctx.getContext('2d'), {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: 'Average Score',
							data: values,
							backgroundColor: 'rgba(54, 115, 255, 0.08)',
							borderColor: 'rgba(54, 115, 255, 1)',
							tension: 0.3,
							fill: true,
							pointRadius: 4
						}]
					},
					options: {
						responsive: true,
						scales: { y: { beginAtZero: true, max: 5 } },
						plugins: { legend: { display: false } }
					}
				});
			}

			// Populate summary stats (simple examples, replace with server calcs later)
			const avg = (values.reduce((s,v)=>s+Number(v),0) / values.length).toFixed(2);
			document.getElementById('stat-average-score').textContent = avg;
			document.getElementById('stat-total-surveys').textContent = (Math.floor(Math.random()*1200)+100); // placeholder
			document.getElementById('stat-top-faculty').textContent = window.__dashboardData.topFaculty[0]?.name ?? '--';
			document.getElementById('stat-departments').textContent = Object.keys(window.__dashboardData.departmentData).length;

			// If backend later sends realtime arrays, we re-render lists: top performers, comments, faculty status
			function renderTopPerformers() {
				const ul = document.getElementById('top-performers-list');
				if (!ul) return;
				ul.innerHTML = '';
				window.__dashboardData.topFaculty.forEach(f=>{
					const li = document.createElement('li');
					li.className = 'list-group-item d-flex justify-content-between align-items-center';
					li.innerHTML = `<div><div class="fw-semibold">${f.name}</div><div class="small text-muted">Score: ${f.score}</div></div><span class="badge bg-success">${f.score}</span>`;
					ul.appendChild(li);
				});
			}

			function renderComments() {
				const container = document.getElementById('comments-list');
				if (!container) return;
				container.innerHTML = '';
				window.__dashboardData.comments.forEach(c=>{
					const col = document.createElement('div');
					col.className = 'col-12 col-md-4';
					col.innerHTML = `<div class="comment-card"><div class="small text-muted">By ${c.author}</div><div class="mt-2">${c.text}</div></div>`;
					container.appendChild(col);
				});
			}

			function renderFacultyStatus() {
				const ul = document.getElementById('faculty-status-list');
				if (!ul) return;
				ul.innerHTML = '';
				window.__dashboardData.facultyStats.forEach(fs=>{
					const li = document.createElement('li');
					li.className = 'list-group-item d-flex justify-content-between align-items-center';
					li.innerHTML = `<div><div class="fw-semibold">${fs.name}</div><div class="small text-muted">${fs.status}</div></div><div class="small text-muted">${fs.score}</div>`;
					ul.appendChild(li);
				});
			}

			// initial render
			renderTopPerformers();
			renderComments();
			renderFacultyStatus();

			// future: hook filters to re-fetch backend data and update window.__dashboardData then re-render
		})();
	</script>
@endsection
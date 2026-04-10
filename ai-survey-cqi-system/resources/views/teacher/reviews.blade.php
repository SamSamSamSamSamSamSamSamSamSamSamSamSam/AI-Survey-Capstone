@extends('layouts.default')

@section('header')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-compact { border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
        .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .badge-custom { font-size: 0.875rem; padding: 0.5rem 1rem; }
        .sentiment-positive { background-color: #d4edda; color: #155724; }
        .sentiment-negative { background-color: #f8d7da; color: #721c24; }
        .sentiment-neutral { background-color: #e2e3e5; color: #383d41; }
        .chart-container { position: relative; height: 300px; }
        .feedback-card { border-left: 4px solid #0d6efd; padding: 1rem; margin-bottom: 1rem; background-color: #fff; border-radius: 0.5rem; }
    </style>
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Faculty Performance Review</h2>
            <p class="text-muted mb-0">{{ auth()->user()->name }}'s Analytics & Feedback</p>
        </div>
        <form action="{{ route('teacher.reviews') }}" method="GET" class="d-flex align-items-center gap-2">
            <label for="semester_id" class="form-label mb-0 fw-500">Semester:</label>
            <select name="semester_id" onchange="this.form.submit()" class="form-select form-select-sm" style="width: 250px;">
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemester->id == $sem->id ? 'selected' : '' }}>
                        {{ $sem->getLabelAttribute() }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Key Performance Indicators --}}
    <div class="row mb-4 g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-compact stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted fw-600 mb-2">
                        <i class="bi bi-star-fill text-warning me-2"></i>Overall Rating
                    </h6>
                    <h3 class="mb-0 text-primary fw-bold">
                        {{ number_format($categoryScores->avg('average'), 2) }}<span class="text-muted fs-6">/5.0</span>
                    </h3>
                    <small class="text-muted">Based on {{ $categoryScores->count() }} categories</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-compact stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted fw-600 mb-2">
                        <i class="bi bi-hand-thumbs-up text-success me-2"></i>Positive Feedback
                    </h6>
                    <h3 class="mb-0 text-success fw-bold">{{ $sentimentStats['positive'] ?? 0 }}</h3>
                    <small class="text-muted">{{ number_format((($sentimentStats['positive'] ?? 0) / max(array_sum((array)$sentimentStats), 1)) * 100, 1) }}% of responses</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-compact stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted fw-600 mb-2">
                        <i class="bi bi-exclamation-circle text-danger me-2"></i>Negative Feedback
                    </h6>
                    <h3 class="mb-0 text-danger fw-bold">{{ $sentimentStats['negative'] ?? 0 }}</h3>
                    <small class="text-muted">{{ number_format((($sentimentStats['negative'] ?? 0) / max(array_sum((array)$sentimentStats), 1)) * 100, 1) }}% of responses</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-compact stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted fw-600 mb-2">
                        <i class="bi bi-chat-square-text text-info me-2"></i>Total Responses
                    </h6>
                    <h3 class="mb-0 text-info fw-bold">{{ $reviewsByCategory->count() }}</h3>
                    <small class="text-muted">Student feedback entries</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Analytics Charts --}}
    <div class="row mb-4 g-3">
        {{-- Category Scores Chart --}}
        <div class="col-12 col-lg-6">
            <div class="card card-compact">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart text-primary me-2"></i>Performance by Category
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sentiment Distribution Chart --}}
        <div class="col-12 col-lg-6">
            <div class="card card-compact">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Sentiment Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="sentimentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown Table --}}
    @if($categoryScores->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-compact">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul text-primary me-2"></i>Detailed Category Scores
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-600">Category</th>
                                    <th class="fw-600">Average Score</th>
                                    <th class="fw-600">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryScores as $score)
                                <tr>
                                    <td class="fw-500">{{ $score->category }}</td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($score->average, 2) }}/5.0</span>
                                    </td>
                                    <td>
                                        @php
                                            $avg = $score->average;
                                            $color = $avg >= 4.0 ? 'success' : ($avg >= 3.0 ? 'warning' : 'danger');
                                            $label = $avg >= 4.0 ? 'Excellent' : ($avg >= 3.0 ? 'Good' : 'Needs Improvement');
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

	{{-- Student Feedback Section with Tabs --}}
	<div class="row">
		<div class="col-12">
			<div class="card card-compact">
				<div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">
						<i class="bi bi-chat-dots text-primary me-2"></i>Student Feedback
					</h5>
				</div>
				<div class="card-body">
					@if($reviewsByCategory->isNotEmpty())
						<ul class="nav nav-tabs mb-4" id="feedbackTabs" role="tablist">
							@foreach($reviewsByCategory as $category => $comments)
								<li class="nav-item" role="presentation">
									<button class="nav-link {{ $loop->first ? 'active' : '' }}" 
										id="tab-{{ Str::slug($category) }}" 
										data-bs-toggle="tab" 
										data-bs-target="#content-{{ Str::slug($category) }}" 
										type="button" role="tab">
										{{ $category }} ({{ $comments->count() }})
									</button>
								</li>
							@endforeach
						</ul>

						<div class="tab-content" id="feedbackTabsContent">
							@foreach($reviewsByCategory as $category => $comments)
								<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
									id="content-{{ Str::slug($category) }}" 
									role="tabpanel">
									
									<div class="feedback-container" style="max-height: 500px; overflow-y: auto;">
										@foreach($comments as $review)
											<div class="feedback-card">
												<div class="d-flex justify-content-between align-items-start mb-2">
													<div>
														<h6 class="mb-1 fw-600">{{ $review->subject->course_code ?? 'N/A' }}</h6>
														<small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
													</div>
													<span class="badge badge-custom 
														{{ $review->sentiment_label === 'positive' ? 'sentiment-positive' : 
														($review->sentiment_label === 'negative' ? 'sentiment-negative' : 'sentiment-neutral') }}">
														{{ ucfirst($review->sentiment_label ?? 'neutral') }}
													</span>
												</div>
												<p class="mb-0 text-dark">{{ $review->response }}</p>
											</div>
										@endforeach
									</div>
								</div>
							@endforeach
						</div>
					@else
						<div class="alert alert-info mb-0" role="alert">
							<i class="bi bi-info-circle me-2"></i>No student feedback yet for this semester.
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Category Scores Bar Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($categoryScores->pluck('category')) !!},
                datasets: [{
                    label: 'Average Score',
                    data: {!! json_encode($categoryScores->pluck('average')) !!},
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            callback: function(value) { return value.toFixed(1); }
                        }
                    }
                }
            }
        });
    }

    // Sentiment Distribution Doughnut Chart
    const sentimentCtx = document.getElementById('sentimentChart');
    if (sentimentCtx) {
        new Chart(sentimentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [
                        {{ $sentimentStats['positive'] ?? 0 }},
                        {{ $sentimentStats['neutral'] ?? 0 }},
                        {{ $sentimentStats['negative'] ?? 0 }}
                    ],
                    backgroundColor: ['#28a745', '#6c757d', '#dc3545'],
                    borderColor: ['#fff', '#fff', '#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
</script>
@endsection
@extends('layouts.default')

@section('content')

{{-- Page Header --}}
<div class="dash-header">
    <div class="dash-header__left">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
        <h1 class="dash-header__title">Faculty Improvement Dashboard</h1>
        <p class="dash-header__subtitle">Monitor performance, sentiment, and evaluation trends across all faculty.</p>
    </div>
    <div class="dash-header__actions">
        <a href="{{ route('admin.surveys.create') }}" class="cbtn cbtn--primary cbtn--sm">
            <i class="bi bi-plus-circle me-1"></i> Create Survey
        </a>
        <a href="{{ route('admin.surveys.index') }}" class="cbtn cbtn--secondary cbtn--sm">
            <i class="bi bi-eye me-1"></i> View Surveys
        </a>
        <a href="{{ route('admin.reports.filter') }}" class="cbtn cbtn--success cbtn--sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> CQI Report
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="dash-filters">
    <div class="dash-filters__selects">

        {{-- Teacher Filter --}}
        <div class="dash-filter-group">
            <label class="dash-filter-group__label" for="teacher-filter">
                <i class="bi bi-person-badge me-1"></i> Teacher
            </label>
            <select id="teacher-filter" class="form-select form-select-sm"
                    onchange="window.location.href = this.value;">
                <option value="{{ route('admin.dashboard', ['semester_id' => request('semester_id')]) }}">
                    All Teachers
                </option>
                @foreach($teachers as $teacher)
                    <option value="{{ route('admin.dashboard', [
                        'teacher_id'  => $teacher->id,
                        'semester_id' => request('semester_id'),
                    ]) }}"
                        {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Semester Filter --}}
        <div class="dash-filter-group">
            <label class="dash-filter-group__label" for="semester-filter">
                <i class="bi bi-calendar2-range me-1"></i> Semester
            </label>
            <select id="semester-filter" class="form-select form-select-sm"
                    onchange="window.location.href = this.value;">
                <option value="{{ route('admin.dashboard', ['teacher_id' => request('teacher_id')]) }}">
                    All Semesters
                </option>
                @foreach($semesters as $sem)
                    <option value="{{ route('admin.dashboard', [
                        'teacher_id'  => request('teacher_id'),
                        'semester_id' => $sem->id,
                    ]) }}"
                        {{ request('semester_id') == $sem->id ? 'selected' : '' }}>
                        {{ $sem->name }}
                        @if($sem->is_active) (Active) @endif
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="dash-filters__links">
        <small class="dash-filters__timestamp text-muted">
            <i class="bi bi-clock me-1"></i> Updated {{ now()->toDateTimeString() }}
        </small>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card kpi-card--primary">
            <div class="kpi-card__icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="kpi-card__body">
                <span class="kpi-card__label">Total Responses</span>
                <span class="kpi-card__value">{{ $distinct_evaluators ?? 0 }}</span>
                <span class="kpi-card__sub">
                    Participation:
                    <strong>{{ $participation_pct ?? 'N/A' }}%</strong>
                    @if($eligible_evaluators)
                        of {{ $eligible_evaluators }} eligible
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card kpi-card--success">
            <div class="kpi-card__icon">
                <i class="bi bi-star-fill"></i>
            </div>
            <div class="kpi-card__body">
                <span class="kpi-card__label">Overall Mean Rating</span>
                <span class="kpi-card__value {{ ($mean ?? 0) >= 4.0 ? 'text-success' : (($mean ?? 0) < 3.0 ? 'text-danger' : 'text-warning') }}">
                    {{ $mean !== null ? number_format($mean, 2) : 'N/A' }}
                </span>
                <span class="kpi-card__sub">Target: <strong>4.0</strong> — Higher is better</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card kpi-card--info">
            <div class="kpi-card__icon">
                <i class="bi bi-chat-heart-fill"></i>
            </div>
            <div class="kpi-card__body">
                <span class="kpi-card__label">Positive Sentiment</span>
                <span class="kpi-card__value">{{ $overallPositivePct ?? 'N/A' }}%</span>
                <span class="kpi-card__sub">Of all qualitative comments</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card kpi-card--warning">
            <div class="kpi-card__icon">
                <i class="bi bi-activity"></i>
            </div>
            <div class="kpi-card__body">
                <span class="kpi-card__label">Standard Deviation</span>
                <span class="kpi-card__value {{ ($stddev ?? 1) < 0.8 ? 'text-success' : 'text-warning' }}">
                    {{ $stddev !== null ? number_format($stddev, 2) : 'N/A' }}
                </span>
                <span class="kpi-card__sub">Lower = more consistent ratings</span>
            </div>
        </div>
    </div>

</div>

{{-- ROW 1: Monthly Trend (line) + Sentiment Donut --}}
<div class="row g-3 mb-4">

    {{-- Monthly Performance Trend — enhanced with target line --}}
    <div class="col-lg-8">
        <div class="dash-card h-100">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Monthly Performance Trend</h5>
                    <p class="dash-card__subtitle">Rating score and sentiment over time — dotted line marks the 4.0 target</p>
                </div>
            </div>
            <div class="dash-card__body">
                <div class="chart-container">
                    <canvas id="monthlyCombinedChart"></canvas>
                </div>
                <p class="dash-card__hint">
                    <i class="bi bi-info-circle me-1"></i>
                    Consistent upward trends indicate continuous faculty improvement.
                </p>
            </div>
        </div>
    </div>

    {{-- Sentiment Donut --}}
    <div class="col-lg-4">
        <div class="dash-card h-100">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Overall Sentiment Split</h5>
                    <p class="dash-card__subtitle">Positive / Neutral / Negative breakdown</p>
                </div>
            </div>
            <div class="dash-card__body d-flex flex-column align-items-center justify-content-center">
                <div class="chart-container chart-container--donut">
                    <canvas id="sentimentDonutChart"></canvas>
                </div>
                <div class="donut-legend mt-3" id="sentimentLegend"></div>
            </div>
        </div>
    </div>

</div>

{{-- ROW 2: Rating Distribution + Faculty Comparison --}}
<div class="row g-3 mb-4">

    {{-- Rating Distribution Histogram --}}
    <div class="col-lg-5">
        <div class="dash-card h-100">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Rating Distribution</h5>
                    <p class="dash-card__subtitle">How responses are spread across 1–5 scores</p>
                </div>
            </div>
            <div class="dash-card__body">
                <div class="chart-container">
                    <canvas id="ratingDistributionChart"></canvas>
                </div>
                <p class="dash-card__hint">
                    <i class="bi bi-info-circle me-1"></i>
                    A healthy distribution clusters at 4–5. Low scores need attention.
                </p>
            </div>
        </div>
    </div>

    {{-- Faculty Comparison Horizontal Bar --}}
    <div class="col-lg-7">
        <div class="dash-card h-100">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Faculty Rating Comparison</h5>
                    <p class="dash-card__subtitle">Top 10 faculty by average rating — dotted line marks the 4.0 target</p>
                </div>
            </div>
            <div class="dash-card__body">
                <div class="chart-container chart-container--bar-h">
                    <canvas id="facultyComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ROW 3: Category Radar + Top Performers table --}}
<div class="row g-3 mb-4">

    {{-- Category Radar --}}
    <div class="col-lg-5">
        <div class="dash-card h-100">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Category Performance Radar</h5>
                    <p class="dash-card__subtitle">Strengths &amp; weaknesses across evaluation categories</p>
                </div>
            </div>
            <div class="dash-card__body d-flex align-items-center justify-content-center">
                <div class="chart-container chart-container--radar">
                    <canvas id="categoryRadarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Performers table --}}
    <div class="col-lg-7">
        <div class="dash-card h-100">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">
                        Top Performing Faculty
                        <i class="bi bi-star-fill text-warning ms-1" style="font-size: 0.85rem;"></i>
                    </h5>
                    <p class="dash-card__subtitle">Based on rating count &amp; sentiment</p>
                </div>
            </div>
            <div class="dash-card__body p-0">
                <div class="table-responsive">
                    <table class="table table-hover dash-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-end">Avg</th>
                                <th class="text-end">Positive</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPerformers as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $p['evaluatee_id']]) }}"
                                       class="dash-table__link">
                                        {{ $p['name'] }}
                                    </a>
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $p['avg_rating'] >= 4.5 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ number_format($p['avg_rating'], 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="{{ $p['positive_pct'] >= 80 ? 'text-primary fw-semibold' : 'text-muted' }}">
                                        {{ $p['positive_pct'] }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3">
                                    <div class="dash-empty">
                                        <i class="bi bi-inbox dash-empty__icon"></i>
                                        <span>Not enough data<br><small>≥3 rating responses required</small></span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ROW 4: Category Performance table (full-width, as fallback/detail) --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Category Performance Summary</h5>
                    <p class="dash-card__subtitle">Average score per evaluation category</p>
                </div>
            </div>
            <div class="dash-card__body p-0">
                <div class="table-responsive">
                    <table class="table table-hover dash-table mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Average Score</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryScores as $cat)
                            <tr>
                                <td class="fw-medium">{{ $cat['category'] }}</td>
                                <td class="text-end">
                                    <span class="fw-semibold {{ $cat['avg'] >= 4.0 ? 'text-success' : 'text-warning' }}">
                                        {{ number_format($cat['avg'], 2) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dash-progress">
                                        <div class="dash-progress__bar {{ $cat['avg'] >= 4.0 ? 'dash-progress__bar--success' : 'dash-progress__bar--warning' }}"
                                             style="width: {{ min(($cat['avg'] / 5) * 100, 100) }}%">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3">
                                    <div class="dash-empty">
                                        <i class="bi bi-inbox dash-empty__icon"></i>
                                        <span>No category rating data available.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 5: Faculty Sentiment Breakdown --}}
<div class="row g-3 mb-2">
    <div class="col-12">
        <div class="dash-card">
            <div class="dash-card__header">
                <div>
                    <h5 class="dash-card__title">Faculty Sentiment Breakdown</h5>
                    <p class="dash-card__subtitle">Top 10 faculty with the most qualitative responses</p>
                </div>
            </div>
            <div class="dash-card__body p-0">
                <div class="table-responsive">
                    <table class="table table-hover dash-table mb-0">
                        <thead>
                            <tr>
                                <th>Faculty</th>
                                <th class="text-center">Total</th>
                                <th class="text-center text-success">Positive</th>
                                <th class="text-center text-danger">Negative</th>
                                <th class="text-center text-warning">Neutral</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sentimentPerPerson as $s)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $s['evaluatee_id']]) }}"
                                       class="dash-table__link">
                                        {{ $s['name'] }}
                                    </a>
                                </td>
                                <td class="text-center text-muted">{{ $s['total'] }}</td>
                                <td class="text-center">
                                    <span class="dash-sentiment dash-sentiment--positive">{{ $s['positive_pct'] }}%</span>
                                </td>
                                <td class="text-center">
                                    <span class="dash-sentiment dash-sentiment--negative">{{ $s['negative_pct'] }}%</span>
                                </td>
                                <td class="text-center">
                                    <span class="dash-sentiment dash-sentiment--neutral">{{ $s['neutral_pct'] }}%</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="dash-empty">
                                        <i class="bi bi-inbox dash-empty__icon"></i>
                                        <span>No qualitative data available.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script>
        const dashboardData = {
            // Monthly trend
            monthlyLabels:     @json($monthlyLabels ?? []),
            monthlyAvg:        @json($monthlyAvg ?? []),
            monthlyPosPct:     @json($monthlyPositivePct ?? []),

            // Sentiment donut
            sentimentTotals: {
                positive: {{ $sentimentTotals['positive'] ?? 0 }},
                neutral:  {{ $sentimentTotals['neutral']  ?? 0 }},
                negative: {{ $sentimentTotals['negative'] ?? 0 }},
            },

            // Rating distribution (1–5 counts pre-computed in controller)
            ratingDistribution: @js($ratingDistribution ?? [0,0,0,0,0]),

            // Faculty comparison
            facultyNames:   @json(collect($topPerformers)->pluck('name')->toArray()),
            facultyRatings: @json(collect($topPerformers)->pluck('avg_rating')->toArray()),

            // Category radar
            categoryLabels: @json(collect($categoryScores)->pluck('category')->toArray()),
            categoryAvgs:   @json(collect($categoryScores)->pluck('avg')->toArray()),
        };
    </script>
    @vite('resources/js/admin/dashboard.js')
@endpush
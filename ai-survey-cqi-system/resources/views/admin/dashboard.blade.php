@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
@endsection

@push('styles')
<style>
    .trend-up   { color: #22c55e; font-size: .72rem; font-weight: 600; }
    .trend-down { color: #ef4444; font-size: .72rem; font-weight: 600; }
    .trend-neu  { color: #94a3b8; font-size: .72rem; font-weight: 600; }

    .kpi-sub {
        font-size: .72rem;
        color: var(--bs-secondary-color, #6c757d);
        margin-top: 2px;
    }

    .survey-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem 0;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .survey-row:last-child { border-bottom: none; }

    [data-bs-theme="dark"] .survey-row {
        border-bottom-color: rgba(255,255,255,.07);
    }

    .survey-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .chart-legend {
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .chart-legend-item {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-size: .75rem;
        color: var(--bs-secondary-color, #6c757d);
    }
    .chart-legend-dot {
        width: 10px; height: 10px; border-radius: 50%;
    }
</style>
@endpush

@section('content')

<div class="dashboard">

    {{-- ===== DASHBOARD HEADER ===== --}}
    <div class="dashboard-header d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="dashboard-title">
                Welcome back, {{ Str::words(auth()->user()->name) }}
                <span class="role-badge">Admin</span>
            </h2>
            <p class="dashboard-subtitle">
                Here's what's happening in your CQI system today.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Survey
            </a>
            <a href="{{ route('admin.cqi-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clipboard-data me-1"></i> CQI Reports
            </a>
        </div>
    </div>

    {{-- ===== KPI CARDS ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--blue">
                        <i class="bi bi-ui-checks-grid"></i>
                    </div>
                    <div>
                        <p class="kpi-label">Total Surveys</p>
                        <h3 class="kpi-value">{{ number_format($totalSurveys) }}</h3>
                        <p class="kpi-sub">
                            <span class="trend-up"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> {{ $liveSurveys }} live now</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--success">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div>
                        <p class="kpi-label">Responses Collected</p>
                        <h3 class="kpi-value">{{ number_format($totalResponses) }}</h3>
                        <p class="kpi-sub">Submitted attempts</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--accent">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div>
                        <p class="kpi-label">CQI Reports Generated</p>
                        <h3 class="kpi-value">{{ number_format($totalReports) }}</h3>
                        <p class="kpi-sub">
                            @if($reportsThisMonth > 0)
                                <span class="trend-up"><i class="bi bi-arrow-up-short"></i> {{ $reportsThisMonth }} this month</span>
                            @else
                                <span class="trend-neu">None this month</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--navy">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <p class="kpi-label">Total Users</p>
                        <h3 class="kpi-value">{{ number_format($totalUsers) }}</h3>
                        <p class="kpi-sub">Registered accounts</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== MAIN GRID ===== --}}
    <div class="row g-3">

        {{-- Analytics Chart --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h5 class="card-title mb-0">Analytics Overview</h5>
                        <span class="badge text-bg-secondary" style="font-size:.7rem; font-weight:500;">
                            Last 12 Months
                        </span>
                    </div>

                    <div class="chart-legend mb-3">
                        <div class="chart-legend-item">
                            <div class="chart-legend-dot" style="background:#3b82f6"></div>
                            Responses
                        </div>
                        <div class="chart-legend-item">
                            <div class="chart-legend-dot" style="background:#a855f7"></div>
                            Surveys Created
                        </div>
                    </div>

                    <div style="position:relative; height:220px;">
                        <canvas id="analyticsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Status --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">System Status</h5>

                    <ul class="status-list">
                        <li>
                            <span class="status-dot {{ $liveSurveys > 0 ? 'success' : 'pending' }}"></span>
                            Survey Module
                            <span class="ms-auto small text-muted">{{ $liveSurveys > 0 ? $liveSurveys . ' live' : 'No live surveys' }}</span>
                        </li>
                        <li>
                            <span class="status-dot {{ $isProcessing ? 'warning' : 'pending' }}"></span>
                            AI Processing
                            <span class="ms-auto small text-muted">{{ $isProcessing ? 'Processing' : 'Idle' }}</span>
                        </li>
                        {{-- <li>
                            <span class="status-dot {{ $dbConnected ? 'success' : 'danger' }}"></span>
                            Database
                            <span class="ms-auto small text-muted">
                                {{ $dbConnected ? 'Connected' : 'Disconnected' }}
                            </span>
                        </li> --}}
                    </ul>

                    <hr class="my-3" style="opacity:.08">

                    {{-- Quick stats summary --}}
                    <div class="row g-2 text-center mb-3">
                        <div class="col-6">
                            <div style="background: rgba(59,130,246,.07); border-radius: 8px; padding: .6rem;">
                                <div class="fw-bold" style="font-size:1.1rem">{{ $totalSurveys }}</div>
                                <div class="text-muted" style="font-size:.7rem">Surveys</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: rgba(168,85,247,.07); border-radius: 8px; padding: .6rem;">
                                <div class="fw-bold" style="font-size:1.1rem">{{ $totalReports }}</div>
                                <div class="text-muted" style="font-size:.7rem">CQI Reports</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.analytics.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-bar-chart me-1"></i> View Full Analytics
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-people me-1"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Surveys --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Recent Surveys</h5>
                        <a href="{{ route('admin.surveys.index') }}"
                           class="btn btn-sm btn-outline-secondary">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    @forelse($recentSurveys as $survey)
                        <div class="survey-row">
                            <div class="survey-dot" style="background: {{ $survey->isLive() ? '#22c55e' : '#94a3b8' }}"></div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold small text-truncate">{{ $survey->title }}</div>
                                <div class="text-muted" style="font-size:.72rem">
                                    Created {{ $survey->created_at->diffForHumans() }}
                                    @if($survey->creator)
                                        · by {{ $survey->creator->name }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @if($survey->isLive())
                                    <span class="badge text-bg-success" style="font-size:.68rem">Live</span>
                                @elseif($survey->is_active && $survey->start_date?->isFuture())
                                    <span class="badge text-bg-warning text-dark" style="font-size:.68rem">Scheduled</span>
                                @elseif(!$survey->is_active)
                                    <span class="badge text-bg-secondary" style="font-size:.68rem">Inactive</span>
                                @else
                                    <span class="badge text-bg-danger" style="font-size:.68rem">Ended</span>
                                @endif
                                <a href="{{ route('admin.surveys.show', $survey) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-2"
                                   style="font-size:.72rem">
                                    View
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4" style="font-size:.875rem">
                            <i class="bi bi-clipboard-x d-block mb-2" style="font-size:1.5rem"></i>
                            No surveys yet. <a href="{{ route('admin.surveys.create') }}">Create one</a>.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

    </div>{{-- /.row --}}

</div>{{-- /.dashboard --}}

@endsection

@push('scripts')
{{-- Chart.js from CDN (only if not already bundled via Vite) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Pass PHP data to JS
    const responseData  = @json(array_column($filledChart, 'count'));
    const surveyData    = @json(array_column($surveysChartFilled, 'count'));
    const labels        = @json(array_column($filledChart, 'month'));

    // Detect dark mode
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor  = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
    const labelColor = isDark ? '#94a3b8' : '#6c757d';

    const ctx = document.getElementById('analyticsChart').getContext('2d');

    // Gradient fills
    const blueGrad = ctx.createLinearGradient(0, 0, 0, 220);
    blueGrad.addColorStop(0,   'rgba(59,130,246,.3)');
    blueGrad.addColorStop(1,   'rgba(59,130,246,.0)');

    const purpleGrad = ctx.createLinearGradient(0, 0, 0, 220);
    purpleGrad.addColorStop(0, 'rgba(168,85,247,.25)');
    purpleGrad.addColorStop(1, 'rgba(168,85,247,.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Responses',
                    data: responseData,
                    borderColor: '#3b82f6',
                    backgroundColor: blueGrad,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                },
                {
                    label: 'Surveys Created',
                    data: surveyData,
                    borderColor: '#a855f7',
                    backgroundColor: purpleGrad,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#e2e8f0' : '#1e293b',
                    bodyColor: isDark ? '#94a3b8' : '#475569',
                    borderColor: isDark ? 'rgba(255,255,255,.1)' : 'rgba(0,0,0,.08)',
                    borderWidth: 1,
                    padding: 10,
                },
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: labelColor, font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: labelColor,
                        font: { size: 11 },
                        precision: 0,
                    },
                },
            },
        },
    });

    // Re-render chart on theme toggle so colours update
    const toggle = document.getElementById('themeToggle');
    if (toggle) {
        toggle.addEventListener('change', () => setTimeout(() => location.reload(), 300));
    }
})();
</script>
@endpush
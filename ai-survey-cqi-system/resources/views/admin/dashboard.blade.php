@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
@endsection

@section('content')

{{-- ===== HEADER ===== --}}
<div class="dashboard-header d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="dashboard-title">
            Welcome back, {{ Str::words(auth()->user()->name, 1, '') }}
            <span class="role-badge">Admin</span>
        </h2>
        <p class="dashboard-subtitle">
            Here's an overview of your CQI system.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Survey
        </a>
        <a href="{{ route('admin.semester-setup.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-calendar2-plus me-1"></i> Semester Setup
        </a>
    </div>
</div>

{{-- ===== ROW 1: KPI CARDS (6 non-duplicated metrics) ===== --}}
<div class="row g-3 mb-4">

    {{-- Live Surveys --}}
    <div class="col-6 col-xl-2">
        <div class="card kpi-card kpi-card--compact h-100">
            <div class="card-body">
                <div class="kpi-icon kpi-icon--blue mb-2" style="width:36px;height:36px;font-size:.9rem;">
                    <i class="bi bi-broadcast"></i>
                </div>
                <p class="kpi-label">Live Surveys</p>
                <h3 class="kpi-value">{{ $liveSurveys }}</h3>
                <p class="kpi-meta">
                    {{ $totalSurveys }} total
                </p>
            </div>
        </div>
    </div>

    {{-- Responses + Completion --}}
    <div class="col-6 col-xl-2">
        <div class="card kpi-card kpi-card--compact h-100">
            <div class="card-body">
                <div class="kpi-icon kpi-icon--success mb-2" style="width:36px;height:36px;font-size:.9rem;">
                    <i class="bi bi-chat-left-text"></i>
                </div>
                <p class="kpi-label">Responses</p>
                <h3 class="kpi-value">{{ number_format($totalResponses) }}</h3>
                <p class="kpi-meta">
                    {{ $completionRate }}% completion
                </p>
            </div>
        </div>
    </div>

    {{-- Global Avg Rating --}}
    <div class="col-6 col-xl-2">
        <div class="card kpi-card kpi-card--compact h-100" style="border-top-color: #f59e0b;">
            <div class="card-body">
                <div class="kpi-icon mb-2" style="width:36px;height:36px;font-size:.9rem;background:rgba(245,158,11,.1);color:#d97706;">
                    <i class="bi bi-star-fill"></i>
                </div>
                <p class="kpi-label">Avg Rating</p>
                <h3 class="kpi-value" style="color: #d97706;">
                    {{ number_format($systemAnalytics->global_avg ?? 0, 2) }}
                    <span style="font-size:.9rem;font-weight:400;color:inherit;opacity:.6">/ 5</span>
                </h3>
                <p class="kpi-meta">System-wide</p>
            </div>
        </div>
    </div>

    {{-- CQI Reports --}}
    <div class="col-6 col-xl-2">
        <div class="card kpi-card kpi-card--compact h-100" style="border-top-color: #a855f7;">
            <div class="card-body">
                <div class="kpi-icon mb-2" style="width:36px;height:36px;font-size:.9rem;background:rgba(168,85,247,.1);color:#7c3aed;">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <p class="kpi-label">CQI Reports</p>
                <h3 class="kpi-value">{{ number_format($totalReports) }}</h3>
                <p class="kpi-meta">
                    @if ($reportsThisMonth > 0)
                        <span class="dash-trend-up">
                            <i class="bi bi-arrow-up-short"></i>{{ $reportsThisMonth }} this month
                        </span>
                    @else
                        None this month
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Positive Sentiment --}}
    <div class="col-6 col-xl-2">
        <div class="card kpi-card kpi-card--compact h-100" style="border-top-color: #22c55e;">
            <div class="card-body">
                <div class="kpi-icon kpi-icon--success mb-2" style="width:36px;height:36px;font-size:.9rem;">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <p class="kpi-label">Positive</p>
                <h3 class="kpi-value" style="color: #16a34a;">
                    {{ number_format($sentimentData['positive'], 1) }}%
                </h3>
                <p class="kpi-meta">
                    <span style="color:#ef4444;">{{ number_format($sentimentData['negative'], 1) }}%</span> neg
                </p>
            </div>
        </div>
    </div>

    {{-- Total Users --}}
    <div class="col-6 col-xl-2">
        <div class="card kpi-card kpi-card--compact h-100">
            <div class="card-body">
                <div class="kpi-icon kpi-icon--navy mb-2" style="width:36px;height:36px;font-size:.9rem;">
                    <i class="bi bi-people"></i>
                </div>
                <p class="kpi-label">Users</p>
                <h3 class="kpi-value">{{ number_format($totalUsers) }}</h3>
                <p class="kpi-meta">Registered</p>
            </div>
        </div>
    </div>

</div>

{{-- ===== ROW 2: CHART + SYSTEM STATUS ===== --}}
<div class="row g-3 mb-4">

    {{-- Activity chart (responses + surveys over 12 months) --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="card-title mb-0">Activity Overview</h5>
                    <span class="badge text-bg-secondary" style="font-size:.7rem;font-weight:500;">
                        Last 12 Months
                    </span>
                </div>

                <div class="dash-chart-legend mb-3">
                    <span class="dash-chart-legend-item">
                        <span class="dash-chart-legend-dot" style="background:#3b82f6"></span>
                        Responses
                    </span>
                    <span class="dash-chart-legend-item">
                        <span class="dash-chart-legend-dot" style="background:#a855f7"></span>
                        Surveys Created
                    </span>
                </div>

                <div style="position:relative;height:220px;">
                    <canvas id="analyticsChart"></canvas>
                </div>

            </div>
        </div>
    </div>

    {{-- System status + quick actions --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">System Status</h5>

                <ul class="status-list mb-3">
                    <li>
                        <span class="status-dot {{ $liveSurveys > 0 ? 'success' : 'pending' }}"></span>
                        Survey Module
                        <span class="ms-auto text-muted-sm">
                            {{ $liveSurveys > 0 ? $liveSurveys . ' live' : 'No live surveys' }}
                        </span>
                    </li>
                    <li>
                        <span class="status-dot {{ $isProcessing ? 'warning' : 'pending' }}"></span>
                        AI Processing
                        <span class="ms-auto text-muted-sm">
                            {{ $isProcessing ? 'Running' : 'Idle' }}
                        </span>
                    </li>
                    <li>
                        <span class="status-dot {{ $dbConnected ? 'success' : 'danger' }}"></span>
                        Database
                        <span class="ms-auto text-muted-sm">
                            {{ $dbConnected ? 'Connected' : 'Disconnected' }}
                        </span>
                    </li>
                    <li>
                        <span class="status-dot {{ $reportsThisMonth > 0 ? 'success' : 'pending' }}"></span>
                        CQI Generation
                        <span class="ms-auto text-muted-sm">
                            {{ $reportsThisMonth > 0 ? $reportsThisMonth . ' this month' : 'None this month' }}
                        </span>
                    </li>
                </ul>

                {{-- Completion rate mini-bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted-sm">Survey completion rate</span>
                        <span class="text-muted-sm fw-600">{{ $completionRate }}%</span>
                    </div>
                    <div class="progress" style="height:5px;">
                        <div class="progress-bar bg-primary" style="width:{{ $completionRate }}%"></div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-bar-chart me-1"></i> View Analytics
                    </a>
                    <a href="{{ route('admin.surveys.global-assign') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-send-check me-1"></i> Deploy Surveys
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== ROW 3: SENTIMENT + FACULTY PERFORMANCE ===== --}}
<div class="row g-3 mb-4">

    {{-- Sentiment doughnut --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">System Sentiment</h5>
                <p class="text-muted-sm mb-3">Aggregate across all open-ended responses</p>

                <div style="position:relative;height:180px;">
                    <canvas id="sentimentChart"></canvas>
                </div>

                <div class="row g-2 mt-3 text-center">
                    <div class="col-4">
                        <div class="dash-sent-stat dash-sent-stat--pos">
                            <div class="dash-sent-stat__val">
                                {{ number_format($sentimentData['positive'], 1) }}%
                            </div>
                            <div class="dash-sent-stat__lbl">Positive</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="dash-sent-stat">
                            <div class="dash-sent-stat__val">
                                {{ number_format($sentimentData['neutral'], 1) }}%
                            </div>
                            <div class="dash-sent-stat__lbl">Neutral</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="dash-sent-stat dash-sent-stat--neg">
                            <div class="dash-sent-stat__val">
                                {{ number_format($sentimentData['negative'], 1) }}%
                            </div>
                            <div class="dash-sent-stat__lbl">Negative</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top faculty performers --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">Top Faculty by Rating</h5>
                    <a href="{{ route('admin.analytics.index') }}"
                       class="btn btn-sm btn-outline-secondary">
                        Full Analytics <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                @if ($topPerformers->isEmpty())
                    <div class="empty-state" style="padding: 24px 0;">
                        <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
                        <p class="empty-state-text">No analytics computed yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table data-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Faculty</th>
                                    <th class="text-center">Avg Rating</th>
                                    <th>Sentiment</th>
                                    <th class="text-center">Responses</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topPerformers as $i => $stat)
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            {{-- <div class="user-avatar-sm dash-rank-{{ $i + 1 }}">
                                                {{ strtoupper(substr($stat->faculty->name, 0, 2)) }}
                                            </div> --}}
                                            <div>
                                                <div class="fw-500">{{ $stat->faculty->name }}</div>
                                                <div class="text-muted-sm text-mono">
                                                    {{ $stat->survey->offering->subject->course_code ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="rating-score {{ ($stat->avg_rating ?? 0) >= 4 ? 'rating-score--high' : (($stat->avg_rating ?? 0) >= 3 ? 'rating-score--mid' : 'rating-score--low') }}">
                                            {{ number_format($stat->avg_rating ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td style="min-width: 120px;">
                                        <div class="sentiment-mini-bar">
                                            <div class="sentiment-mini-bar__track">
                                                <div class="sentiment-mini-bar__fill sentiment-mini-bar__fill--pos"
                                                     style="width: {{ $stat->positive_sentiment_percent ?? 0 }}%"></div>
                                                <div class="sentiment-mini-bar__fill sentiment-mini-bar__fill--neu"
                                                     style="width: {{ $stat->neutral_sentiment_percent ?? 0 }}%"></div>
                                                <div class="sentiment-mini-bar__fill sentiment-mini-bar__fill--neg"
                                                     style="width: {{ $stat->negative_sentiment_percent ?? 0 }}%"></div>
                                            </div>
                                            <div class="sentiment-mini-bar__labels">
                                                <span class="sentiment-mini-bar__label--pos">{{ number_format($stat->positive_sentiment_percent ?? 0, 0) }}%</span>
                                                <span class="sentiment-mini-bar__label--neg">{{ number_format($stat->negative_sentiment_percent ?? 0, 0) }}%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="count-badge">{{ $stat->response_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.analytics.show', $stat->id) }}"
                                           class="btn btn-sm btn-icon" title="View Analytics">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ===== ROW 4: RECENT SURVEYS ===== --}}
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title mb-0">Recent Surveys</h5>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-sm btn-outline-secondary">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @forelse ($recentSurveys as $survey)
            <div class="dash-survey-row">
                <div class="dash-survey-row__dot"
                     style="background: {{ $survey->trashed() ? '#94a3b8' : ($survey->is_active ? '#22c55e' : '#94a3b8') }}">
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-500 text-truncate" style="font-size:.875rem;">
                        {{ $survey->title }}
                    </div>
                    <div class="text-muted-sm">
                        <span class="text-mono">
                            {{ $survey->offering?->subject?->course_code ?? '—' }}
                        </span>
                        · {{ $survey->created_at->diffForHumans() }}
                        @if ($survey->creator)
                            · by {{ $survey->creator->name }}
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    @if ($survey->is_active)
                        <span class="status-pill status-pill--active" style="font-size:.67rem;padding:2px 7px;">
                            Active
                        </span>
                    @else
                        <span class="status-pill status-pill--inactive" style="font-size:.67rem;padding:2px 7px;">
                            Inactive
                        </span>
                    @endif
                    <a href="{{ route('admin.surveys.show', $survey) }}"
                       class="btn btn-sm btn-icon" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <i class="bi bi-clipboard-x d-block mb-2 text-muted" style="font-size:1.5rem;"></i>
                <p class="text-muted-sm mb-2">No surveys yet.</p>
                <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Create First Survey
                </a>
            </div>
        @endforelse

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    const isDark     = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor  = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.05)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';
    const tooltipBg  = isDark ? '#1e293b' : '#fff';
    const tooltipBdr = isDark ? 'rgba(255,255,255,.1)' : 'rgba(0,0,0,.08)';
    const tooltipTxt = isDark ? '#e2e8f0' : '#1e293b';

    // ── Sentiment doughnut ──
    new Chart(document.getElementById('sentimentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [
                    @json($sentimentData['positive']),
                    @json($sentimentData['neutral']),
                    @json($sentimentData['negative'])
                ],
                backgroundColor: ['#22c55e', '#94a3b8', '#ef4444'],
                hoverOffset: 4,
                borderWidth: 0,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { display: false } },
        },
    });

    // ── Activity line chart ──
    const responseData = @json(array_column($filledChart, 'count'));
    const surveyData   = @json(array_column($surveysChartFilled, 'count'));
    const labels       = @json(array_column($filledChart, 'month'));

    const ctx         = document.getElementById('analyticsChart').getContext('2d');
    const blueGrad    = ctx.createLinearGradient(0, 0, 0, 220);
    blueGrad.addColorStop(0, 'rgba(59,130,246,.25)');
    blueGrad.addColorStop(1, 'rgba(59,130,246,0)');

    const purpleGrad  = ctx.createLinearGradient(0, 0, 0, 220);
    purpleGrad.addColorStop(0, 'rgba(168,85,247,.2)');
    purpleGrad.addColorStop(1, 'rgba(168,85,247,0)');

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
                    backgroundColor: tooltipBg,
                    titleColor: tooltipTxt,
                    bodyColor: labelColor,
                    borderColor: tooltipBdr,
                    borderWidth: 1,
                    padding: 10,
                },
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: labelColor, font: { size: 10 }, maxRotation: 35, autoSkip: true, maxTicksLimit: 6 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: labelColor, font: { size: 10 }, precision: 0 },
                },
            },
        },
    });

    // Re-render on theme toggle
    document.getElementById('themeToggle')?.addEventListener('change', function () {
        setTimeout(() => location.reload(), 300);
    });

})();
</script>
@endpush
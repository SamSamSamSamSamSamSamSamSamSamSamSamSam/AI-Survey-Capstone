@extends('layouts.app')
@section('title', 'Faculty Dashboard')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
@endsection

@section('content')

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (! $activeSemester)
    <div class="info-notice info-notice--warning mb-4">
        <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
        <div>No active semester is currently set. Contact the administrator.</div>
    </div>
@endif

{{-- ===== WELCOME ===== --}}
<div class="dashboard-header d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="dashboard-title">
            Welcome back, {{ auth()->user()->name }}
            <span class="role-badge">Faculty</span>
        </h2>
        <p class="dashboard-subtitle">
            @if ($activeSemester)
                <span class="status-pill status-pill--active" style="font-size:.72rem;">
                    <i class="bi bi-calendar-check me-1"></i>{{ $activeSemester->full_label }}
                </span>
            @endif
        </p>
    </div>
    <a href="{{ route('faculty.reports.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-clipboard-data me-1"></i> My CQI Reports
    </a>
</div>

{{-- ===== KPI CARDS ===== --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon kpi-icon--blue">
                    <i class="bi bi-book"></i>
                </div>
                <div>
                    <p class="kpi-label">Active Courses</p>
                    <h3 class="kpi-value">{{ $activeOfferings->count() }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100" style="border-top-color: #22c55e;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon kpi-icon--success">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <p class="kpi-label">Students This Semester</p>
                    <h3 class="kpi-value">{{ $activeOfferings->sum('enrollments_count') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100" style="border-top-color: #a855f7;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background: rgba(168,85,247,.1); color: #7c3aed;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div>
                    <p class="kpi-label">Active Surveys</p>
                    <h3 class="kpi-value">{{ $activeSurveys->count() }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100" style="border-top-color: #f59e0b;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon kpi-icon--accent">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <p class="kpi-label">Overall Avg Rating</p>
                    <h3 class="kpi-value">
                        {{ $overallAvgRating ? number_format($overallAvgRating, 2) : '—' }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== COURSES + SURVEYS GRID ===== --}}
<div class="row g-3 mb-4">

    {{-- My Courses --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">My Courses This Semester</h5>
                    <span class="count-badge">{{ $activeOfferings->count() }}</span>
                </div>
                @if ($activeOfferings->isEmpty())
                    <div class="empty-state" style="padding: 24px 0;">
                        <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
                        <p class="empty-state-text">No courses assigned this semester.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table data-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Group</th>
                                    <th class="text-center">Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activeOfferings as $offering)
                                <tr>
                                    <td>
                                        <div class="fw-500 text-mono" style="font-size:.8rem;">
                                            {{ $offering->subject->course_code }}
                                        </div>
                                        <div class="text-muted-sm">
                                            {{ Str::limit($offering->subject->name, 28) }}
                                        </div>
                                    </td>
                                    <td class="text-muted-sm">
                                        {{ $offering->group_number ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="count-badge">{{ $offering->enrollments_count }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            {{-- Pagination Links Container --}}
            @if ($activeOfferings->hasPages())
                <div class="mt-3 pt-2 border-top d-flex justify-content-center dashboard-pagination">
                    {{ $activeOfferings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Survey Status --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Survey Status</h5>
                        <span class="count-badge">{{ $taughtSurveys->total() }}</span>
                    </div>

                    @if ($taughtSurveys->isEmpty())
                        <div class="empty-state" style="padding: 24px 0;">
                            <div class="empty-state-icon"><i class="bi bi-clipboard-x"></i></div>
                            <p class="empty-state-text">No surveys for your courses yet.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach ($taughtSurveys as $survey)
                            <div class="faculty-survey-row">
                                <div class="faculty-survey-row__info">
                                    <span class="text-mono" style="font-size:.78rem;">
                                        {{ $survey->offering->subject->course_code }}
                                    </span>
                                    <span class="text-muted-sm">
                                        {{ Str::limit($survey->title, 38) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="count-badge {{ $survey->is_active ? 'count-badge--responses' : '' }}">
                                        {{ $survey->attempts_count }}
                                    </span>
                                    
                                    @if($survey->is_active)
                                        <span class="status-pill status-pill--active" style="font-size:.67rem; padding: 2px 7px;">
                                            Active
                                        </span>
                                    @else
                                        <span class="status-pill status-pill--inactive" style="font-size:.67rem; padding: 2px 7px;">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Pagination Links Container --}}
                @if ($taughtSurveys->hasPages())
                    <div class="mt-auto pt-2 border-top d-flex justify-content-center dashboard-pagination">
                        {{ $taughtSurveys->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ===== ANALYTICS PREVIEW ===== --}}
@if ($analyticsRecords->isNotEmpty())
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">
        Analytics Preview — {{ $activeSemester?->full_label ?? 'This Semester' }}
    </p>
    <a href="{{ route('faculty.reports.index') }}" class="btn btn-sm btn-outline-secondary">
        View CQI Reports <i class="bi bi-arrow-right ms-1"></i>
    </a>
</div>

<div class="row g-3 mb-4">

    {{-- Category scores --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Average Scores by Category</h5>
                @if (empty($avgCategoryScores))
                    <p class="text-muted-sm">No category data yet.</p>
                @else
                    <div class="category-score-list">
                        @php $weightSvc = app(\App\Services\CategoryWeightService::class); @endphp
                        @foreach ($avgCategoryScores as $cat => $score)
                        @php
                            $pct    = min(100, round(($score / 5) * 100));
                            $interp = $weightSvc->interpret($pct);
                        @endphp
                        <div class="category-score-row">
                            <div class="category-score-row__header">
                                <span class="category-score-row__name">{{ $cat }}</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="category-score-row__interp category-score-row__interp--{{ $interp['cls'] }}">
                                        {{ $interp['label'] }}
                                    </span>
                                    <span class="category-score-row__val">{{ number_format($score, 2) }}</span>
                                </div>
                            </div>
                            <div class="category-score-row__track">
                                <div class="category-score-row__fill category-score-row__fill--{{ $interp['cls'] }}"
                                     style="width: 0%"
                                     data-width="{{ $pct }}%">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sentiment summary --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Sentiment Summary</h5>

                <div class="d-flex gap-4 mb-4">
                    <div>
                        <div class="survey-stat__value">{{ number_format($totalResponses) }}</div>
                        <div class="survey-stat__label">Total Responses</div>
                    </div>
                    <div>
                        <div class="survey-stat__value {{ $overallAvgRating >= 3.5 ? '' : 'survey-stat__value--muted' }}">
                            {{ $overallAvgRating ? number_format($overallAvgRating, 2) : '—' }}
                        </div>
                        <div class="survey-stat__label">Overall Avg Rating</div>
                    </div>
                </div>

                @if ($avgPositivePct !== null)
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="faculty-sentiment-pill faculty-sentiment-pill--pos">
                                <div class="faculty-sentiment-pill__pct">
                                    {{ number_format($avgPositivePct, 1) }}%
                                </div>
                                <div class="faculty-sentiment-pill__lbl">Positive</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="faculty-sentiment-pill faculty-sentiment-pill--neu">
                                <div class="faculty-sentiment-pill__pct">
                                    {{ number_format($avgNeutralPct ?? 0, 1) }}%
                                </div>
                                <div class="faculty-sentiment-pill__lbl">Neutral</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="faculty-sentiment-pill faculty-sentiment-pill--neg">
                                <div class="faculty-sentiment-pill__pct">
                                    {{ number_format($avgNegativePct ?? 0, 1) }}%
                                </div>
                                <div class="faculty-sentiment-pill__lbl">Negative</div>
                            </div>
                        </div>
                    </div>

                    {{-- Per-survey breakdown --}}
                    @if ($analyticsRecords->count() > 1)
                    <div class="mt-3 pt-3 border-top">
                        <p class="card-section-label mb-2">Per Survey</p>
                        @foreach ($analyticsRecords->take(4) as $rec)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom"
                             style="font-size:.78rem;">
                            <span class="text-mono">{{ $rec->survey->offering->subject->course_code }}</span>
                            <span class="rating-score {{ ($rec->avg_rating ?? 0) >= 3.5 ? 'rating-score--high' : 'rating-score--mid' }}">
                                {{ $rec->avg_rating ? number_format($rec->avg_rating, 2) : '—' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    <p class="text-muted-sm mt-2">
                        Sentiment data will be available after surveys are closed and analytics are computed.
                    </p>
                @endif

            </div>
        </div>
    </div>

</div>
@endif

{{-- ===== CQI REPORTS + ALL-TIME ===== --}}
<div class="row g-3">

    {{-- Recent CQI Reports --}}
    @if ($cqiReports->isNotEmpty())
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">Recent CQI Reports</h5>
                    <a href="{{ route('faculty.reports.index') }}" class="btn btn-sm btn-outline-secondary"
                       style="font-size:.72rem;">View All</a>
                </div>
                @foreach ($cqiReports as $report)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <div class="cqi-report-icon flex-shrink-0">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-500" style="font-size:.845rem;">{{ Str::limit($report->title, 48) }}</div>
                        <div class="text-muted-sm">
                            {{ $report->survey?->offering?->semester?->full_label }} ·
                            {{ $report->created_at->format('M d, Y') }}
                            @if ($report->is_regenerated)
                                · <span class="regen-badge"><i class="bi bi-arrow-repeat me-1"></i>Regenerated</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <a href="{{ route('faculty.reports.show', $report->id) }}"
                           class="btn btn-sm btn-icon" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('faculty.reports.download', $report->id) }}"
                           class="btn btn-sm btn-icon" title="Download PDF">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- All-time summary --}}
    <div class="{{ $cqiReports->isNotEmpty() ? 'col-lg-4' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">All-time Summary</h5>
                <div class="d-flex flex-column gap-3 mt-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon kpi-icon--navy flex-shrink-0">
                            <i class="bi bi-collection"></i>
                        </div>
                        <div>
                            <div class="survey-stat__value" style="font-size:1.5rem;">{{ $totalOfferings }}</div>
                            <div class="survey-stat__label">Total Courses Handled</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon kpi-icon--success flex-shrink-0">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div class="survey-stat__value" style="font-size:1.5rem; color: #16a34a;">
                                {{ $totalStudentsTaught }}
                            </div>
                            <div class="survey-stat__label">Unique Students Taught</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon flex-shrink-0" style="background: rgba(168,85,247,.1); color: #7c3aed;">
                            <i class="bi bi-clipboard-data"></i>
                        </div>
                        <div>
                            <div class="survey-stat__value" style="font-size:1.5rem; color: #7c3aed;">
                                {{ $cqiReports->count() }}
                            </div>
                            <div class="survey-stat__label">CQI Reports Generated</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.category-score-row__fill').forEach(function (el) {
        const target = el.dataset.width;
        requestAnimationFrame(function () {
            setTimeout(function () { el.style.width = target; }, 120);
        });
    });
})();
</script>
@endpush
@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
@endsection

@section('content')

{{-- ===== NO ACTIVE SEMESTER ===== --}}
@if (! $activeSemester)
    <div class="info-notice info-notice--warning mb-4">
        <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
        <div>No active semester is currently set. Enrollment and surveys are unavailable.</div>
    </div>
@endif

{{-- ===== WELCOME + KPI ===== --}}
<div class="dashboard-header d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="dashboard-title">
            Welcome back, {{ Str::words(auth()->user()->name, 1, '') }}
            <span class="role-badge">Student</span>
        </h2>
        <p class="dashboard-subtitle">
            @if ($activeSemester)
                <span class="status-pill status-pill--active" style="font-size:.72rem;">
                    <i class="bi bi-calendar-check me-1"></i>{{ $activeSemester->full_label }}
                </span>
            @else
                No active semester
            @endif
        </p>
    </div>
    <a href="{{ route('survey.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-clipboard-check me-1"></i> My Surveys
    </a>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon kpi-icon--blue">
                    <i class="bi bi-book"></i>
                </div>
                <div>
                    <p class="kpi-label">Enrolled This Semester</p>
                    <h3 class="kpi-value">{{ $activeSemEnrolled }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100" style="border-top-color: #f59e0b;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon kpi-icon--accent">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <p class="kpi-label">Pending Surveys</p>
                    <h3 class="kpi-value" style="{{ $pendingCount > 0 ? 'color: #d97706;' : '' }}">
                        {{ $pendingCount }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100" style="border-top-color: #22c55e;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon kpi-icon--success">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <p class="kpi-label">Surveys Completed</p>
                    <h3 class="kpi-value">{{ $totalCompleted }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100" style="border-top-color: {{ $pendingCount > 0 ? '#ef4444' : '#22c55e' }};">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background: rgba({{ $pendingCount > 0 ? '239,68,68' : '34,197,94' }},.1); color: {{ $pendingCount > 0 ? '#dc2626' : '#16a34a' }};">
                    <i class="bi bi-{{ $pendingCount > 0 ? 'bell-fill' : 'check-circle-fill' }}"></i>
                </div>
                <div>
                    <p class="kpi-label">{{ $pendingCount > 0 ? 'Action Required' : 'All Done!' }}</p>
                    <h3 class="kpi-value">{{ $pendingCount }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== PENDING SURVEYS ===== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">
        <i class="bi bi-hourglass-split me-2 text-muted"></i>
        Pending Surveys
        @if ($pendingCount > 0)
            <span class="ms-2 count-badge">{{ $pendingCount }}</span>
        @endif
    </p>
    <a href="{{ route('survey.index') }}" class="btn btn-sm btn-outline-secondary">
        View All <i class="bi bi-arrow-right ms-1"></i>
    </a>
</div>

@if ($pendingSurveys->isEmpty())
    <div class="card mb-4">
        <div class="empty-state">
            <div class="empty-state-icon" style="background: rgba(#22c55e,.1); color: #16a34a;">
                <i class="bi bi-check-circle"></i>
            </div>
            <p class="empty-state-text">You're all caught up — no pending surveys!</p>
        </div>
    </div>
@else
    <div class="student-survey-list mb-4">
        @foreach ($pendingSurveys as $survey)
        @php
            $daysLeft  = $survey->end_date ? now()->diffInDays($survey->end_date, false) : null;
            $isUrgent  = $daysLeft !== null && $daysLeft <= 2;
        @endphp
        <div class="student-survey-card {{ $isUrgent ? 'student-survey-card--urgent' : '' }}">
            <div class="student-survey-card__body">
                <div class="fw-500 mb-1">{{ $survey->title }}</div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="category-tag"><i class="bi bi-book me-1"></i>{{ $survey->offering->subject->course_code }}</span>
                    <span class="category-tag"><i class="bi bi-person me-1"></i>{{ $survey->offering->teacher->name }}</span>
                    <span class="category-tag">{{ $survey->questions_count }} question(s)</span>
                    @if ($survey->end_date)
                        <span class="student-deadline-chip {{ $daysLeft !== null && $daysLeft > 3 ? 'student-deadline-chip--safe' : '' }}">
                            <i class="bi bi-clock me-1"></i>
                            @if ($daysLeft <= 0) Closes today
                            @elseif ($daysLeft === 1) Closes tomorrow
                            @else Closes {{ $survey->end_date->format('M d, Y') }}
                            @endif
                        </span>
                    @endif
                    @if ($isUrgent)
                        <span class="student-deadline-chip">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Urgent
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('survey.take', $survey->id) }}" class="btn btn-primary btn-sm flex-shrink-0">
                Take Survey <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endforeach
    </div>
@endif

{{-- ===== ENROLLED COURSES + COMPLETED ===== --}}
<div class="row g-3">

    {{-- Enrolled courses --}}
    <div class="{{ $completedAttempts->isNotEmpty() ? 'col-lg-7' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">
                        My Enrolled Courses
                        <span class="text-muted fw-400 ms-1" style="font-size:.78rem;">
                            {{ $viewAll ? 'All Semesters' : ($activeSemester?->full_label ?? '') }}
                        </span>
                    </h5>
                    @if ($activeSemester)
                        <a href="{{ route('student.dashboard', ['all_semesters' => ! $viewAll]) }}"
                           class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;">
                            {{ $viewAll ? 'Active Only' : 'All Semesters' }}
                        </a>
                    @endif
                </div>

                @if ($enrollments->isEmpty())
                    <div class="empty-state" style="padding: 24px 0;">
                        <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
                        <p class="empty-state-text">No enrollments found.</p>
                        @if (! $viewAll && $activeSemester)
                            <a href="{{ route('student.enrollments.index') }}"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Enroll Now
                            </a>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table data-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Faculty</th>
                                    <th>Type</th>
                                    @if ($viewAll)<th>Semester</th>@endif
                                    <th>Survey</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($enrollments as $enrollment)
                                @php
                                    $offeringSurvey = $pendingSurveys->firstWhere('offering_id', $enrollment->offering_id);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-500 text-mono" style="font-size:.8rem;">
                                            {{ $enrollment->offering->subject->course_code }} - Group {{$enrollment->offering->group_number}}
                                        </div>
                                        <div class="text-muted-sm">
                                            {{ Str::limit($enrollment->offering->subject->name, 28) }}
                                        </div>
                                    </td>
                                    <td class="text-muted-sm">{{ $enrollment->offering->teacher->name }}</td>
                                    <td>
                                        @if ($enrollment->enrollmentType)
                                            <span class="role-pill {{ $enrollment->enrollmentType->name === 'Block-Enrolled' ? 'role-pill--faculty' : 'role-pill--student' }}">
                                                {{ $enrollment->enrollmentType->name === 'Block-Enrolled' ? 'Block' : 'Individual' }}
                                            </span>
                                        @endif
                                    </td>
                                    @if ($viewAll)
                                        <td class="text-muted-sm">
                                            {{ $enrollment->offering->semester->full_label }}
                                        </td>
                                    @endif
                                    <td>
                                        @if ($offeringSurvey)
                                            <a href="{{ route('survey.take', $offeringSurvey->id) }}"
                                               class="student-deadline-chip">
                                                <i class="bi bi-hourglass-split me-1"></i>Pending
                                            </a>
                                        @else
                                            <span class="text-muted-sm">—</span>
                                        @endif
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

    {{-- Completed surveys --}}
    @if ($completedAttempts->isNotEmpty())
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">Recently Completed</h5>
                    <a href="{{ route('survey.index') }}" class="btn btn-sm btn-outline-secondary"
                       style="font-size:.72rem;">View All</a>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach ($completedAttempts as $attempt)
                    <div class="d-flex align-items-start gap-3 py-2 border-bottom">
                        <div class="flex-shrink-0 mt-1">
                            <span class="verified-badge verified-badge--yes">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-500" style="font-size:.845rem;">
                                {{ Str::limit($attempt->survey->title, 40) }}
                            </div>
                            <div class="text-muted-sm">
                                {{ $attempt->survey->offering->subject->course_code }} ·
                                {{ $attempt->submitted_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@endsection
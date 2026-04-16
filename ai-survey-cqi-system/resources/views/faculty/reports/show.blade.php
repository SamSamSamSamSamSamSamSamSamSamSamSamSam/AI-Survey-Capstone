@extends('layouts.app')
@section('title', 'CQI Report')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('faculty.reports.index') }}">My Reports</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($cqiReport->title, 36) }}</li>
</ol>
@endsection

@section('content')

@php $ai = $cqiReport->report_text; @endphp

{{-- ===== HEADER ===== --}}
<div class="page-header flex-wrap gap-2">
    <div>
        <h2 class="page-heading">CQI Report</h2>
        <p class="page-subheading">{{ $cqiReport->title }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('faculty.reports.download', $cqiReport->id) }}"
           class="btn btn-sm btn-primary">
            <i class="bi bi-download me-1"></i> Download PDF
        </a>
        <a href="{{ route('faculty.reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== META CARD ===== --}}
<div class="card mb-4" style="border-left: 4px solid {{ config('brand.primary', '#0a3d62') }};">
    <div class="card-body p-0">
        <div class="detail-row">
            <span class="detail-label">
                <i class="bi bi-book me-2 text-muted"></i>Course
            </span>
            <span class="detail-value fw-500">
                {{ $cqiReport->survey?->offering?->subject?->course_code }}
                — {{ $cqiReport->survey?->offering?->subject?->name }}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">
                <i class="bi bi-calendar3 me-2 text-muted"></i>Semester
            </span>
            <span class="detail-value">{{ $cqiReport->survey?->offering?->semester?->full_label }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">
                <i class="bi bi-layers me-2 text-muted"></i>Scope
            </span>
            <span class="detail-value">
                <span class="scope-badge scope-badge--{{ $cqiReport->scope_type }}">
                    {{ ucfirst($cqiReport->scope_type) }}
                </span>
            </span>
        </div>
        <div class="detail-row detail-row--last">
            <span class="detail-label">
                <i class="bi bi-calendar-event me-2 text-muted"></i>Generated On
            </span>
            <span class="detail-value text-muted-sm">
                {{ $cqiReport->created_at->format('F d, Y') }}
            </span>
        </div>
    </div>
</div>

{{-- ===== OVERALL INTERPRETATION ===== --}}
@if (!empty($ai['overall_interpretation']))
<div class="cqi-section cqi-section--interpretation mb-4">
    <div class="cqi-section__header">
        <div class="cqi-section__icon cqi-section__icon--blue">
            <i class="bi bi-lightbulb"></i>
        </div>
        <h3 class="cqi-section__title">Overall Interpretation</h3>
    </div>
    <p class="cqi-section__body">{{ $ai['overall_interpretation'] }}</p>
</div>
@endif

{{-- ===== STRENGTHS + IMPROVEMENTS ===== --}}
<div class="row g-3 mb-4">

    @if (!empty($ai['strengths']))
    <div class="col-md-6">
        <div class="card cqi-list-card cqi-list-card--strengths h-100">
            <div class="card-body">
                <div class="cqi-list-card__header">
                    <div class="cqi-list-card__icon cqi-list-card__icon--green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5 class="cqi-list-card__title">Strengths Identified</h5>
                </div>
                <ul class="cqi-bullet-list">
                    @foreach ($ai['strengths'] as $s)
                        <li class="cqi-bullet-list__item cqi-bullet-list__item--green">{{ $s }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if (!empty($ai['areas_for_improvement']))
    <div class="col-md-6">
        <div class="card cqi-list-card cqi-list-card--improvement h-100">
            <div class="card-body">
                <div class="cqi-list-card__header">
                    <div class="cqi-list-card__icon cqi-list-card__icon--amber">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5 class="cqi-list-card__title">Areas for Improvement</h5>
                </div>
                <ul class="cqi-bullet-list">
                    @foreach ($ai['areas_for_improvement'] as $a)
                        <li class="cqi-bullet-list__item cqi-bullet-list__item--amber">{{ $a }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ===== ACTION PLAN ===== --}}
@if (!empty($ai['action_plan']))
<div class="card mb-4">
    <div class="card-body">
        <div class="cqi-list-card__header mb-3">
            <div class="cqi-list-card__icon cqi-list-card__icon--blue">
                <i class="bi bi-list-task"></i>
            </div>
            <h5 class="cqi-list-card__title">Action Plan</h5>
        </div>
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Action</th>
                        <th>Responsible</th>
                        <th>Timeline</th>
                        <th>Expected Outcome</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ai['action_plan'] as $ap)
                    <tr>
                        <td><span class="category-tag">{{ $ap['area'] ?? '' }}</span></td>
                        <td style="font-size:.875rem;">{{ $ap['action'] ?? '' }}</td>
                        <td class="text-muted-sm">{{ $ap['responsible_person'] ?? '' }}</td>
                        <td>
                            <span class="timeline-badge">
                                <i class="bi bi-clock me-1"></i>{{ $ap['timeline'] ?? '' }}
                            </span>
                        </td>
                        <td class="text-muted-sm">{{ $ap['expected_outcome'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ===== MONITORING ===== --}}
@if (!empty($ai['monitoring']))
<div class="card mb-4">
    <div class="card-body">
        <div class="cqi-list-card__header mb-3">
            <div class="cqi-list-card__icon cqi-list-card__icon--blue">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h5 class="cqi-list-card__title">Monitoring and Evaluation</h5>
        </div>
        <ul class="cqi-bullet-list">
            @foreach ($ai['monitoring'] as $m)
                <li class="cqi-bullet-list__item cqi-bullet-list__item--green">{{ $m }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- ===== CONCLUSION ===== --}}
@if (!empty($ai['conclusion']))
<div class="cqi-section cqi-section--conclusion mb-4">
    <div class="cqi-section__header">
        <div class="cqi-section__icon cqi-section__icon--navy">
            <i class="bi bi-journal-check"></i>
        </div>
        <h3 class="cqi-section__title">Conclusion</h3>
    </div>
    <p class="cqi-section__body">{{ $ai['conclusion'] }}</p>
</div>
@endif

@endsection
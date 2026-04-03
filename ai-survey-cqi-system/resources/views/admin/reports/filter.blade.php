@extends('layouts.default')

@section('content')

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">Generate CQI Report</h1>
        <p class="page-subtitle">Select a semester and survey to generate a Continuous Quality Improvement report.</p>
    </div>
</div>

{{-- Flash error --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ── Step 1: Pick semester ───────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="cqi-step-card">
            <div class="cqi-step-header">
                <span class="cqi-step-number">1</span>
                <div>
                    <h5 class="cqi-step-title">Select Semester</h5>
                    <p class="cqi-step-desc">Choose the academic semester you want to generate the report for.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.reports.filter') }}" id="semesterForm">
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Academic Semester</label>
                        <select name="semester_id" class="form-select" id="semesterSelect"
                                onchange="document.getElementById('semesterForm').submit()">
                            <option value="">— Choose a semester —</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}"
                                    {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->label }}
                                    @if($semester->is_active)
                                        (Active)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Step 2: Pick survey ─────────────────────────────────────────────── --}}
    @if($selectedSemesterId)
        <div class="col-12">
            <div class="cqi-step-card">
                <div class="cqi-step-header">
                    <span class="cqi-step-number">2</span>
                    <div>
                        <h5 class="cqi-step-title">Select Survey</h5>
                        <p class="cqi-step-desc">Each row represents one faculty member evaluated for one subject and group.</p>
                    </div>
                </div>

                @if($surveys->isEmpty())
                    <div class="cqi-empty-state">
                        <i class="bi bi-inbox cqi-empty-icon"></i>
                        <p>No surveys found for this semester.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table cqi-survey-table">
                            <thead>
                                <tr>
                                    <th>Faculty Member</th>
                                    <th>Subject</th>
                                    <th>Group</th>
                                    <th>Survey Title</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($surveys as $survey)
                                    <tr>
                                        <td>
                                            <div class="cqi-faculty-cell">
                                                <div class="cqi-faculty-avatar">
                                                    {{ strtoupper(substr($survey->evaluatee?->name ?? '?', 0, 1)) }}
                                                </div>
                                                <span>{{ $survey->evaluatee?->name ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $survey->subject?->course_code }}</span>
                                            <br>
                                            <small class="text-muted">{{ $survey->subject?->name }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Group {{ $survey->group ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $survey->title }}</td>
                                        <td>
                                            @if($survey->is_active)
                                                <span class="badge cqi-badge-active">Active</span>
                                            @else
                                                <span class="badge cqi-badge-closed">Closed</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($hasApiKey)
                                                <a href="{{ route('admin.reports.pdf.cqi_report', $survey->id) }}"
                                                   class="btn btn-primary btn-sm cqi-generate-btn"
                                                   onclick="return confirmGenerate(this)">
                                                    <i class="bi bi-file-earmark-text me-1"></i>
                                                    Generate Report
                                                </a>
                                            @else
                                                <span data-bs-toggle="tooltip"
                                                      title="No AI API key configured. Go to Settings to add one.">
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="bi bi-lock-fill me-1"></i>
                                                        Generate Report
                                                    </button>
                                                </span>
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
    @endif

</div>

{{-- Generating overlay --}}
<div class="cqi-overlay" id="generatingOverlay">
    <div class="cqi-overlay-card">
        <div class="cqi-spinner">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        <h5 class="mt-3 mb-1">Generating CQI Report</h5>
        <p class="text-muted mb-0">Analyzing evaluation data and consulting AI&hellip;</p>
        <p class="text-muted small">This may take up to 30 seconds.</p>
        <!-- Close button -->
        <button type="button" class="btn btn-secondary mt-3" onclick="closeOverlay()">Close</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function confirmGenerate(link) {
        document.getElementById('generatingOverlay').classList.add('is-visible');
        return true;
    }

    // Simple close function
    function closeOverlay() {
        document.getElementById('generatingOverlay').classList.remove('is-visible');
    }
</script>
@endpush
@extends('layouts.app')
@section('title', 'Semester Rollover')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Semester Rollover</li>
</ol>
@endsection

@section('content')

@php
    $uploadMax = ini_get('upload_max_filesize');
    $execTime  = ini_get('max_execution_time');
    $configOk  = (int) $uploadMax >= 10 && (int) $execTime >= 60;
@endphp

@if (! $configOk)
    <div class="info-notice info-notice--warning mb-4">
        <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
        <div>
            <strong>PHP configuration may be too low</strong> —
            <code>upload_max_filesize={{ $uploadMax }}</code>,
            <code>max_execution_time={{ $execTime }}s</code>.
            Large CSVs may fail. Increase these in <code>php.ini</code>.
        </div>
    </div>
@endif

<div class="wizard-layout">

    {{-- ===== LEFT: Progress Rail ===== --}}
    <div class="wizard-rail">

        <div class="wizard-rail__header">
            <div class="wizard-rail__title">Semester Rollover</div>
            <div class="wizard-rail__semester">
                {{ $activeSemester->semester_name }} {{ $activeSemester->academic_year }}
            </div>
        </div>

        @foreach ($steps as $num => $s)
        @php
            $isCompleted = ($stats[$num] ?? 0) > 0;
            $isActive    = $num == $step;
            $isDisabled  = $num > $step && ! $isCompleted;
            $state       = $isActive ? 'active' : ($isCompleted ? 'done' : 'pending');
        @endphp
        <a href="{{ $isDisabled ? '#' : route('admin.semester-setup.index', ['step' => $num]) }}"
           class="wizard-step wizard-step--{{ $state }} {{ $isDisabled ? 'wizard-step--disabled' : '' }}">
            <div class="wizard-step__dot wizard-step__dot--{{ $state }}">
                @if ($isCompleted && ! $isActive)
                    <i class="bi bi-check-lg"></i>
                @else
                    {{ $num }}
                @endif
            </div>
            <div class="wizard-step__info">
                <span class="wizard-step__label"><i class="bi {{ $s['icon'] }}"></i>  {{ $s['label'] }}</span>
                <span class="wizard-step__count">
                    {{ number_format($stats[$num] ?? 0) }} record(s)
                </span>
            </div>
        </a>
        @endforeach

        @php
            $completedCount = collect($stats)->filter(fn($v) => $v > 0)->count();
            $totalSteps     = count($steps);
            $progressPct    = $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0;
        @endphp
        <div class="wizard-rail__footer">
            <div class="d-flex justify-content-between mb-1">
                <span class="wizard-rail__progress-label">Overall Progress</span>
                <span class="wizard-rail__progress-pct">{{ $progressPct }}%</span>
            </div>
            <div class="wizard-rail__progress-track">
                <div class="wizard-rail__progress-fill" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>

    </div>

    {{-- ===== RIGHT: Step Card ===== --}}
    <div class="wizard-content">
        <div class="wizard-card">

        <div class="wizard-actions mt-3 d-flex align-items-center justify-content-between">
            
            <div class="d-flex gap-2">
                {{-- BACK BUTTON --}}
                @if($step > 1)
                    <a href="{{ route('admin.semester-setup.index', ['step' => $step - 1]) }}" 
                    class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @endif

                {{-- MAIN IMPORT BUTTON (Only visible when file is ready) --}}
                <button type="button" id="btnImport" class="btn btn-primary d-none">
                    <span class="btn-text"><i class="bi bi-database-check me-2"></i> Confirm &amp; Import</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span> Importing…
                    </span>
                </button>
            </div>

            {{-- NEXT BUTTON (Visible if step is already done or as a 'Skip' alternative) --}}
            @if($step < count($steps))
                @php $isCurrentStepDone = ($stats[$step] ?? 0) > 0; @endphp
                
                <a href="{{ route('admin.semester-setup.index', ['step' => $step + 1]) }}" 
                class="btn {{ $isCurrentStepDone ? 'btn-primary' : 'btn-link text-secondary' }} text-decoration-none">
                    {{ $isCurrentStepDone ? 'Next Step' : 'Skip to Next' }}
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @endif

        </div>

            <div class="wizard-card__body">

                {{-- CSV Spec --}}
                <div class="csv-spec mb-4">
                    <div class="csv-spec__header">
                        <i class="bi bi-filetype-csv me-2"></i>
                        Required Columns —
                        <code class="csv-spec__filename">{{ $steps[$step]['key'] }}.csv</code>
                    </div>
                    <table class="csv-spec__table">
                        <thead>
                            <tr><th>Column</th><th>Example</th><th>Notes</th></tr>
                        </thead>
                        <tbody>
                            @if ($step == 1)
                                <tr><td><code>user_id_number</code></td><td>2021-00123</td><td>Must be unique</td></tr>
                                <tr><td><code>name</code></td><td>Juan dela Cruz</td><td></td></tr>
                                <tr><td><code>email</code></td><td>juan@school.edu</td><td>Must be unique</td></tr>
                            @elseif ($step == 2)
                                <tr><td><code>block_name</code></td><td>BSIT-4A</td><td>Unique per semester</td></tr>
                                <tr><td><code>program_code</code></td><td>BSIT</td><td>Must exist</td></tr>
                                <tr><td><code>year_level</code></td><td>4</td><td>Integer 1–5</td></tr>
                            @elseif ($step == 3)
                                <tr><td><code>subject_code</code></td><td>IT4101</td><td>Must exist</td></tr>
                                <tr><td><code>teacher_id_number</code></td><td>2010-00001</td><td>Faculty account</td></tr>
                                <tr><td><code>group_number</code></td><td>1</td><td><span class="csv-spec__optional">optional</span></td></tr>
                            @elseif ($step == 4)
                                <tr><td><code>student_id_number</code></td><td>2021-00123</td><td>Must exist</td></tr>
                                <tr><td><code>subject_code</code></td><td>IT4101</td><td>Offering must exist</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Upload form --}}
                <form id="wizardUploadForm"
                      action="{{ route('admin.semester-setup.preview') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="step" value="{{ $step }}">

                    @include('admin.semester-setup._upload_field', [
                        'stepId' => $step,
                        'label'  => $steps[$step]['key'] . '.csv',
                    ])

                    {{-- Validation results (shown after AJAX preview) --}}
                    <div id="validationSection" class="d-none mt-4">
                        <div id="validationBox" class="wizard-validation-box"></div>
                        <div class="wizard-actions mt-3">
                            <button type="button" id="btnImport"
                                    class="btn btn-primary d-none">
                                <span class="btn-text">
                                    <i class="bi bi-database-check me-2"></i> Confirm &amp; Import
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Importing…
                                </span>
                            </button>
                            <button type="button" id="btnRetry"
                                    class="btn btn-outline-secondary d-none">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Choose Another File
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>

{{-- Loading overlay --}}
<div class="wizard-loading-overlay d-none" id="wizardLoader">
    <div class="wizard-loading-overlay__inner">
        <div class="spinner-border text-primary mb-3" style="width:2.5rem;height:2.5rem;border-width:3px;"></div>
        <p class="wizard-loading-overlay__text" id="loaderText">Validating CSV…</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.WIZARD_CONFIG = {
    step:         {{ $step }},
    previewUrl:   "{{ route('admin.semester-setup.preview') }}",
    importRoutes: {
        1: "{{ route('admin.semester-setup.import-students') }}",
        2: "{{ route('admin.semester-setup.import-blocks') }}",
        3: "{{ route('admin.semester-setup.import-offerings') }}",
        4: "{{ route('admin.semester-setup.import-enrollments') }}"
    }
};
</script>
    @vite(['resources/js/modules/wizard-upload.js'])
    {{-- <script src="{{ asset('js/modules/wizard-upload.js') }}" defer></script> --}}
@endpush

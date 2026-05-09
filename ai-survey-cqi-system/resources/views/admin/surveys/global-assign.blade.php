@extends('layouts.app')
@section('title', 'Global Survey Assignment')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item active">Global Assignment</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Survey Deployment Center</h2>
        <p class="page-subheading">Deploy evaluation surveys to all course offerings at once.</p>
    </div>
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Surveys
    </a>
</div>

@if (! $activeSemester)

    <div class="info-notice info-notice--warning">
        <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
        <div>
            No active semester is set. Please activate a semester before assigning surveys globally.
        </div>
    </div>

@else

<div class="global-assign-grid">

    {{-- ===== LEFT: Configure & Launch ===== --}}
    <div class="card">
        <div class="template-card-header">
            {{-- <i class="bi bi-rocket-takeoff me-2 text-muted"></i> --}}
            <h5>Configure &amp; Launch</h5>
        </div>
        <div class="card-body">

            {{-- No official template warning --}}
            @if (! $officialTemplate)
                <div class="info-notice info-notice--danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
                    <div>
                        No official template found. Please create and mark a template as the
                        official questionnaire first.
                        <a href="{{ route('admin.survey-templates.create') }}" class="fw-600 ms-1">
                            Create Template →
                        </a>
                    </div>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.surveys.global-assign.store') }}"
                  id="globalAssignForm"
                  data-confirm="Create surveys for all {{ $offeringsWithoutSurvey }} offering(s)? They will be activated immediately.">
                @csrf

            {{-- Template Selection (Official Surveys Only) --}}
            <div class="mb-4">
                <label for="template_id" class="form-label">Template</label>
                
                <div class="input-group">
                    {{-- <span class="input-group-text bg-light">
                        <i class="bi bi-star-fill text-warning"></i>
                    </span> --}}
                    <div class="col-md-6">
                        <select 
                            name="template_id" 
                            id="template_id" 
                            class="form-select @error('template_id') is-invalid @enderror"
                        >
                            <option value="" selected disabled>Select an official survey...</option>
                            
                            @forelse ($officialTemplate as $template)
                                <option value="{{ $template->id }}" {{ old('template_id', $currentTemplateId ?? ($loop->first ? $template->id : '')) == $template->id ? 'selected' : '' }}>
                                    @if ($template->is_official) ★ @else ☆ @endif{{ $template->name }}
                                </option>
                            @empty
                                <option value="" disabled>No official templates available</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                @error('template_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <div class="form-text">Choose from the authorized university questionnaires.</div>
            </div>

                {{-- Target role --}}
                <div class="mb-4">
                    <label class="form-label" for="target_role_id">
                        Target Role <span class="text-danger">*</span>
                    </label>
                    <div class="col-md-6">
                        <select name="target_role_id" id="target_role_id"
                                class="form-select @error('target_role_id') is-invalid @enderror">
                            <option value="">Who will take these surveys?</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    @selected(old('target_role_id') == $role->id || $role->name === 'student')>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('target_role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Survey period --}}
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label" for="start_date">
                            Start Date &amp; Time <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="start_date" id="start_date"
                            class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}"
                            min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="end_date">
                            End Date &amp; Time <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="end_date" id="end_date"
                            class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date') }}"
                            min="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Skip existing toggle --}}
                <div class="card border shadow-sm mb-4 col-md-6">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="me-3">
                            <h6 class="mb-1 fw-bold">Skip existing surveys</h6>
                            <p class="text-muted small mb-0">Offerings that already have a survey won't be affected.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="skip_existing" value="0">
                            <input class="form-check-input h4 mb-0" type="checkbox" name="skip_existing" 
                                value="1" id="skipExistingSwitch" checked style="cursor: pointer;">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <button type="submit"
                            class="btn btn-primary w-100"
                            @disabled(! $officialTemplate)>
                        {{-- <i class="bi bi-rocket-takeoff me-2"></i> --}}
                        <i class="bi bi-send-check-fill"></i>
                        Assign Surveys to All Offerings
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ===== RIGHT: Summary + How it works ===== --}}
    <div class="d-flex flex-column gap-3">

        {{-- Active semester summary --}}
        <div class="card">
            <div class="template-card-header">
                <i class="bi bi-calendar3 me-2 text-muted"></i>
                Active Semester Summary
            </div>
            <div class="card-body p-0">
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bi bi-calendar-check me-2 text-muted"></i>Semester
                    </span>
                    <span class="detail-value fw-500">{{ $activeSemester->full_label }}</span>
                </div>
                <div class="detail-row detail-row--last">
                    <span class="detail-label">
                        <i class="bi bi-easel me-2 text-muted"></i>Offerings without survey
                    </span>
                    <span class="detail-value">
                        @if ($offeringsWithoutSurvey > 0)
                            <span class="global-assign-count">
                                {{ $offeringsWithoutSurvey }}
                            </span>
                            <span class="text-muted-sm ms-2">will receive a survey</span>
                        @else
                            <span class="status-pill status-pill--active">
                                <i class="bi bi-check-circle me-1"></i>All covered
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Add a "Deployment Preview" Alert --}}
        <div id="deployment-preview" class="alert alert-info border-0 shadow-sm mb-1 d-none">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div>
                    <h6 class="mb-0 fw-600">Deployment Summary</h6>
                    <p class="mb-0 small text-muted">
                        You are about to deploy <span id="preview-count" class="fw-bold">{{ $offeringsWithoutSurvey }}</span> 
                        surveys to <span id="preview-role" class="fw-bold">Students</span>.
                    </p>
                </div>
            </div>
        </div>

        {{-- How it works --}}
        <div class="card">
            <div class="template-card-header">
                <i class="bi bi-info-circle me-2 text-muted"></i>
                How This Works
            </div>
            <div class="card-body">
                <ul class="wizard-howto-list">
                    <li>
                        <span class="wizard-howto-list__dot wizard-howto-list__dot--blue"></span>
                        One survey is created per course offering using the official template.
                    </li>
                    <li>
                        <span class="wizard-howto-list__dot wizard-howto-list__dot--blue"></span>
                        Questions are copied from the template into each survey.
                    </li>
                    <li>
                        <span class="wizard-howto-list__dot wizard-howto-list__dot--green"></span>
                        All surveys are immediately <strong>activated</strong> with the period you set.
                    </li>
                    <li>
                        <span class="wizard-howto-list__dot wizard-howto-list__dot--amber"></span>
                        Surveys auto-deactivate when the end date passes (scheduled command).
                    </li>
                    <li>
                        <span class="wizard-howto-list__dot wizard-howto-list__dot--amber"></span>
                        When deactivated, analytics computation is triggered automatically.
                    </li>
                </ul>
            </div>
        </div>

    </div>

</div>{{-- /.global-assign-grid --}}

@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const roleSelect = document.getElementById('target_role_id');
    const previewDiv = document.getElementById('deployment-preview');
    const previewRole = document.getElementById('preview-role');

    // 1. Logic: Ensure end date is always after start date
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });

    // 2. UX: Show/Update Deployment Preview
    const updatePreview = () => {
        if(roleSelect.value) {
            previewDiv.classList.remove('d-none');
            previewRole.innerText = roleSelect.options[roleSelect.selectedIndex].text;
        } else {
            previewDiv.classList.add('d-none');
        }
    };

    roleSelect.addEventListener('change', updatePreview);
    updatePreview(); // Run on load
});
</script>
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
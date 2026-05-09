@extends('layouts.app')
@section('title', 'Create Survey')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Create Survey</h2>
        <p class="page-subheading">Set up a new course evaluation survey.</p>
    </div>
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Surveys
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul style="list-style-type: none;">
            @foreach ($errors->all() as $error)
                <li><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="form-page-layout">
    <div class="form-card">

        {{-- No active semester notice --}}
        @if (! $activeSemester)
            <div class="info-notice mb-4">
                <i class="bi bi-info-circle-fill info-notice__icon"></i>
                <div>No active semester is set — showing all available offerings.</div>
            </div>
        @endif
        <div id="semester-info" 
            data-name="{{ $activeSemester->full_label ?? 'No Active Semester' }}" 
            class="d-none">
        </div>

        <form method="POST" action="{{ route('admin.surveys.store') }}" novalidate>
            @csrf

            {{-- Template selector --}}
            <div class="mb-4">
                <label class="form-label" for="template_id">
                    Template
                    <span class="form-label-optional">optional</span>
                </label>
                <select name="template_id" id="template_id" class="form-select">
                    <option value="">— No template / build from scratch —</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}"
                                data-name="{{ $template->name }}"
                                @selected(old('template_id') == $template->id)>
                            @if ($template->is_official) ★ @else ☆ @endif{{ $template->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">
                    Selecting a template will copy its questions into this survey automatically.
                </div>
            </div>

            <hr class="form-divider">

            @include('admin.surveys._form')

            <div class="form-actions mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Survey
                </button>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const templateSelect = document.getElementById('template_id');
    const titleInput     = document.getElementById('survey_title');
    
    // Grab the value from the data-attribute we placed in the Blade file
    const semesterLabel  = document.getElementById('semester-info')?.dataset.name || '';

    if (templateSelect && titleInput) {
        templateSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            
            // Only update if a template is selected AND the input is empty
            if (selected.value && !titleInput.value.trim()) {
                const templateName = selected.dataset.name || '';
                
                // Concatenate the name and semester
                titleInput.value = `End of Semester Evaluation (${semesterLabel})`;
            }
        });
    }
})();
</script>
@endpush
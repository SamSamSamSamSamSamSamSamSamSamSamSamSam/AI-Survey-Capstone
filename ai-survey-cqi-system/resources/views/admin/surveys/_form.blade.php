{{-- TomSelect CSS --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"> --}}

@php
    // Determine if we are editing and if the survey is currently active
    $isEdit = isset($survey);
    $isLocked = $isEdit && $survey->is_active;
@endphp

{{-- Course Offering --}}
<div class="mb-4">
    <label class="form-label" for="offering_id">
        Course Offerings <span class="text-danger">*</span>
    </label>
    
    {{-- We use multiple select for Create, but check your logic if Edit only supports one --}}
    <select name="offering_id[]" id="searchable-select"
            class="form-select @error('offering_id') is-invalid @enderror"
            placeholder="Type to add courses..." 
            multiple autocomplete="off"
            {{ $isLocked ? 'disabled' : '' }}>
        @foreach ($offerings as $offering)
            <option value="{{ $offering->id }}"
                @selected(in_array($offering->id, (array) old('offering_id', $isEdit ? $survey->offering_id : [])))>
                {{ $offering->subject->course_code }} — {{ $offering->subject->name }}
                | {{ $offering->teacher->name }}
            </option>
        @endforeach
    </select>

    @if($isLocked)
        <div class="form-text text-muted">
            <i class="bi bi-lock-fill"></i> Locked while survey is active.
        </div>
        {{-- Hidden fields ensure the data is still sent even if the select is disabled --}}
        @foreach((array)$survey->offering_id as $id)
            <input type="hidden" name="offering_id[]" value="{{ $id }}">
        @endforeach
    @endif

    @error('offering_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Target Role --}}
<div class="mb-4">
    <label class="form-label" for="target_role_id">
        Target Role <span class="text-danger">*</span>
    </label>
    <select name="target_role_id" id="target_role_id"
            class="form-select @error('target_role_id') is-invalid @enderror"
            {{ $isLocked ? 'disabled' : '' }}>
        <option value="">Who will take this survey?</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}"
                @selected(old('target_role_id', $survey->target_role_id ?? '') == $role->id)>
                {{ ucfirst($role->name) }}
            </option>
        @endforeach
    </select>

    @if($isLocked)
        <div class="form-text text-muted">
            <i class="bi bi-lock-fill"></i> Locked while survey is active.
        </div>
        <input type="hidden" name="target_role_id" value="{{ $survey->target_role_id }}">
    @endif

    @error('target_role_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Survey Title --}}
<div class="mb-4">
    <label class="form-label" for="survey_title">
        Survey Title <span class="text-danger">*</span>
    </label>
    <input type="text" name="title" id="survey_title"
           class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $survey->title ?? '') }}"
           placeholder="e.g. End-of-Semester Faculty Evaluation"
           required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Survey period --}}
<div class="row g-3 mb-4">
    <div class="col-12 pb-0 mb-n2">
        <span class="form-text text-muted">
            <i class="bi bi-info-circle-fill text-primary me-1"></i> 
            Optional: Set a start and end date to schedule this survey.
        </span>
    </div>

    <div class="col-6 mt-2"> 
        <label class="form-label" for="start_date">Start Date &amp; Time</label>
        <input type="datetime-local" name="start_date" id="start_date"
            class="form-control @error('start_date') is-invalid @enderror"
            value="{{ old('start_date', (isset($survey) && $survey->start_date) ? $survey->start_date->format('Y-m-d\TH:i') : '') }}">
        @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-6 mt-2">
        <label class="form-label" for="end_date">End Date &amp; Time</label>
        <input type="datetime-local" name="end_date" id="end_date"
            class="form-control @error('end_date') is-invalid @enderror"
            value="{{ old('end_date', (isset($survey) && $survey->end_date) ? $survey->end_date->format('Y-m-d\TH:i') : '') }}">
        @error('end_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Description --}}
<div class="mb-4">
    <label class="form-label" for="description">
        Description
        <span class="form-label-optional">optional</span>
    </label>
    <textarea name="description" id="description" rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Optional instructions for respondents…">{{ old('description', $survey->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- TomSelect JS --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script> --}}
<script>
    // Wait for the browser to finish loading everything
    document.addEventListener('DOMContentLoaded', function() {
        
        // Now TomSelect is available via the window object
        var select = new TomSelect("#searchable-select", {
            plugins: ['remove_button'],
            create: false,
            persist: false,
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            }
        });

        // PHP logic still works fine here
        @if(isset($isLocked) && $isLocked)
            select.disable();
        @endif
    });
</script>
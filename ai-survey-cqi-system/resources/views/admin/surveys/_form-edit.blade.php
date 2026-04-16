{{-- ============================================================
     admin/surveys/_form.blade.php
     Shared by create.blade.php and edit.blade.php
     ============================================================ --}}

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">


{{-- Template selector added to Edit --}}
{{-- DOES NOT UPDATE YET NEED FIX --}}
{{-- <div class="mb-4">
    <label class="form-label" for="template_id">
        Template
        <span class="form-label-optional">optional</span>
    </label>
    <select name="template_id" id="template_id" class="form-select">
        <option value="">— Keep current questions / No template —</option>
        @foreach ($templates as $template)
            <option value="{{ $template->id }}"
                    data-name="{{ $template->name }}"
                    @selected(old('template_id', $survey->template_id ?? '') == $template->id)>
                @if ($template->is_official) ⭐ @endif{{ $template->name }}
            </option>
        @endforeach
    </select>
    <div class="form-text text-warning">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Changing the template will replace all existing questions in this survey.
    </div>
</div>

<hr class="form-divider"> --}}

{{-- Course Offering --}}
<div class="mb-4">
    <label class="form-label" for="offering_id">
        Course Offering <span class="text-danger">*</span>
    </label>
    <select name="offering_id" id="searchable-select"
        class="form-select @error('offering_id') is-invalid @enderror"
        {{ isset($survey) && $survey->is_active ? 'disabled' : '' }}>
        @foreach ($offerings as $offering)
            <option value="{{ $offering->id }}"
                @selected(old('offering_id', $survey->offering_id ?? '') == $offering->id)>
                {{ $offering->subject->course_code }} — {{ $offering->subject->name }}
                | {{ $offering->teacher->name }}
            </option>
        @endforeach
    </select>
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
            {{ isset($survey) && $survey->is_active ? 'disabled' : '' }}>
        <option value="">Who will take this survey?</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}"
                @selected(old('target_role_id', $survey->target_role_id ?? '') == $role->id)>
                {{ ucfirst($role->name) }}
            </option>
        @endforeach
    </select>
    @error('target_role_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Preserve value when disabled --}}
@if(isset($survey) && $survey->is_active)
    <input type="hidden" name="offering_id" value="{{ $survey->offering_id }}">
    <input type="hidden" name="target_role_id" value="{{ $survey->target_role_id }}">
@endif

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

{{-- Survey period (Edit Mode) --}}
<div class="row g-3 mb-4">
    <div class="col-6">
        <label class="form-label" for="start_date">
            Start Date &amp; Time 
            <span class="form-label-optional">optional</span>
        </label>
        <input type="datetime-local" name="start_date" id="start_date"
            class="form-control @error('start_date') is-invalid @enderror"
            value="{{ old('start_date', $survey->start_date ? $survey->start_date->format('Y-m-d\TH:i') : '') }}">
        @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-6">
        <label class="form-label" for="end_date">
            End Date &amp; Time 
            <span class="form-label-optional">optional</span>
        </label>
        <input type="datetime-local" name="end_date" id="end_date"
            class="form-control @error('end_date') is-invalid @enderror"
            value="{{ old('end_date', $survey->end_date ? $survey->end_date->format('Y-m-d\TH:i') : '') }}">
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


<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect("#searchable-select", {
        plugins: ['remove_button'], // Adds the "X" to remove a selected item
        create: false,
        persist: false,
        onItemAdd: function() {
            this.setTextboxValue(''); // Clears search text after selecting one
            this.refreshOptions();
        }
    });
</script>
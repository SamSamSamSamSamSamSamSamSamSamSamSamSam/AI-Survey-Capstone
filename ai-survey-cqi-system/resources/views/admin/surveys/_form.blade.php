{{-- ============================================================
     admin/surveys/_form.blade.php
     Shared by create.blade.php and edit.blade.php
     ============================================================ --}}

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

{{-- Course Offering --}}
<div class="mb-4">
    <label class="form-label" for="offering_id">
        Course Offerings <span class="text-danger">*</span>
    </label>
    <select name="offering_id[]" id="searchable-select"
            class="form-select @error('offering_id') is-invalid @enderror"
            placeholder="Type to add courses..." 
            multiple autocomplete="off">
        @foreach ($offerings as $offering)
            <option value="{{ $offering->id }}"
                @selected(in_array($offering->id, (array) old('offering_id', isset($survey) ? $survey->offering_id : [])))>
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
            class="form-select @error('target_role_id') is-invalid @enderror">
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
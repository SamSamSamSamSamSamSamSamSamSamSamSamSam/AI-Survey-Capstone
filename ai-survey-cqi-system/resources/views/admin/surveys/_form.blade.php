{{-- resources/views/admin/surveys/_form.blade.php --}}

<div class="form-group">
    <label class="form-label">Course Offering <span style="color:#dc2626">*</span></label>
    <select name="offering_id" class="form-control {{ $errors->has('offering_id') ? 'is-invalid' : '' }}">
        <option value="">Select offering…</option>
        @foreach ($offerings as $offering)
            <option value="{{ $offering->id }}" @selected(old('offering_id', $survey->offering_id ?? '') == $offering->id)>
                {{ $offering->subject->course_code }} — {{ $offering->subject->name }}
                | {{ $offering->semester->full_label }}
                | {{ $offering->teacher->name }}
                @if ($offering->group_number) (Group {{ $offering->group_number }}) @endif
            </option>
        @endforeach
    </select>
    @error('offering_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Target Role <span style="color:#dc2626">*</span></label>
    <select name="target_role_id" class="form-control {{ $errors->has('target_role_id') ? 'is-invalid' : '' }}">
        <option value="">Who will take this survey?</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" @selected(old('target_role_id', $survey->target_role_id ?? '') == $role->id)>
                {{ ucfirst($role->name) }}
            </option>
        @endforeach
    </select>
    @error('target_role_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Survey Title <span style="color:#dc2626">*</span></label>
    <input type="text" name="title"
           class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
           value="{{ old('title', $survey->title ?? '') }}"
           placeholder="e.g. End-of-Semester Faculty Evaluation">
    @error('title') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3"
              class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
              placeholder="Optional instructions for respondents…">{{ old('description', $survey->description ?? '') }}</textarea>
    @error('description') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

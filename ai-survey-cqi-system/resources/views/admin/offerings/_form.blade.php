{{-- resources/views/admin/offerings/_form.blade.php --}}

<div class="form-group">
    <label class="form-label">Subject <span style="color:#dc2626">*</span></label>
    <select name="subject_id" class="form-control {{ $errors->has('subject_id') ? 'is-invalid' : '' }}">
        <option value="">Select subject…</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->id }}" @selected(old('subject_id', $offering->subject_id ?? '') == $subject->id)>
                {{ $subject->course_code }} — {{ $subject->name }}
            </option>
        @endforeach
    </select>
    @error('subject_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Semester <span style="color:#dc2626">*</span></label>
    <select name="semester_id" class="form-control {{ $errors->has('semester_id') ? 'is-invalid' : '' }}">
        <option value="">Select semester…</option>
        @foreach ($semesters as $sem)
            <option value="{{ $sem->id }}" @selected(old('semester_id', $offering->semester_id ?? '') == $sem->id)>
                {{ $sem->full_label }} {{ $sem->is_active ? '(Active)' : '' }}
            </option>
        @endforeach
    </select>
    @error('semester_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Faculty / Teacher <span style="color:#dc2626">*</span></label>
    <select name="teacher_id" class="form-control {{ $errors->has('teacher_id') ? 'is-invalid' : '' }}">
        <option value="">Select faculty…</option>
        @foreach ($teachers as $teacher)
            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $offering->teacher_id ?? '') == $teacher->id)>
                {{ $teacher->name }} ({{ $teacher->user_id_number }})
            </option>
        @endforeach
    </select>
    @error('teacher_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div style="display:flex;gap:1rem;">
    <div class="form-group" style="flex:1;">
        <label class="form-label">Offering Type</label>
        <select name="offering_type_id" class="form-control">
            <option value="">None</option>
            @foreach ($offeringTypes as $type)
                <option value="{{ $type->id }}" @selected(old('offering_type_id', $offering->offering_type_id ?? '') == $type->id)>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group" style="flex:1;">
        <label class="form-label">Group Number</label>
        <input type="number" name="group_number" min="1"
               class="form-control {{ $errors->has('group_number') ? 'is-invalid' : '' }}"
               value="{{ old('group_number', $offering->group_number ?? '') }}"
               placeholder="e.g. 1">
        <p class="form-text">Optional — for multiple sections of the same subject.</p>
        @error('group_number') <p class="invalid-feedback">{{ $message }}</p> @enderror
    </div>
</div>

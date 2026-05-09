{{-- ============================================================
     admin/offerings/_form.blade.php
     Shared by create.blade.php and edit.blade.php
     ============================================================ --}}

{{-- Subject --}}
<div class="mb-4">
    <label class="form-label" for="subject_id">
        Subject <span class="text-danger">*</span>
    </label>
    <select name="subject_id" id="subject_id"
            class="form-select @error('subject_id') is-invalid @enderror">
        <option value="">Select subject…</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->id }}"
                @selected(old('subject_id', $offering->subject_id ?? '') == $subject->id)>
                {{ $subject->course_code }} — {{ $subject->name }}
            </option>
        @endforeach
    </select>
    @error('subject_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Semester --}}
<div class="mb-4">
    <label class="form-label" for="semester_id">
        Semester <span class="text-danger">*</span>
    </label>
    <select name="semester_id" id="semester_id"
            class="form-select @error('semester_id') is-invalid @enderror">
        <option value="">Select semester…</option>
        @foreach ($semesters as $sem)
            <option value="{{ $sem->id }}"
                @selected(old('semester_id', $offering->semester_id) == $sem->id)>
                {{ $sem->full_label }}{{ $sem->is_active ? ' · Active' : '' }}
            </option>
        @endforeach
    </select>
    @error('semester_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Faculty --}}
<div class="mb-4">
    <label class="form-label" for="teacher_id">
        Faculty / Teacher <span class="text-danger">*</span>
    </label>
    <select name="teacher_id" id="teacher_id"
            class="form-select @error('teacher_id') is-invalid @enderror">
        <option value="">Select faculty…</option>
        @foreach ($teachers as $teacher)
            <option value="{{ $teacher->id }}"
                @selected(old('teacher_id', $offering->teacher_id ?? $activeSemesterId) == $teacher->id)>
                {{ $teacher->name }} ({{ $teacher->user_id_number }})
            </option>
        @endforeach
    </select>
    @error('teacher_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Offering Type + Group --}}
<div class="row g-3 mb-4">
    <div class="col-6">
        <label class="form-label" for="offering_type_id">Offering Type</label>
        <select name="offering_type_id" id="offering_type_id" class="form-select">
            <option value="">None</option>
            @foreach ($offeringTypes as $type)
                <option value="{{ $type->id }}"
                    @selected(old('offering_type_id', $offering->offering_type_id ?? '') == $type->id)>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-6">
        <label class="form-label" for="group_number">Group Number <span class="text-danger">*</span></label>
        <input type="number" name="group_number" id="group_number" min="1"
               class="form-control @error('group_number') is-invalid @enderror"
               value="{{ old('group_number', $offering->group_number ?? '') }}"
               placeholder="e.g. 1">
        @error('group_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
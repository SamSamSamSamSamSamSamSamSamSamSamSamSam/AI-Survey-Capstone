{{-- resources/views/admin/semesters/_form.blade.php --}}
{{--
    Edit form: only the display name is editable.
    academic_start_year and semester_number are immutable after creation
    (they define the semester's identity and are shown read-only in edit.blade.php).
--}}

<div class="mb-4">
    <label class="form-label" for="name">
        Display Name <span class="text-danger">*</span>
    </label>
    <p class="form-text mt-0 mb-1">
        A human-readable label for this semester, e.g. <em>1st Semester S.Y. 2025–2026</em>.
    </p>
    <input type="text"
           name="name"
           id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $semester->name ?? '') }}"
           placeholder="e.g. 1st Semester S.Y. 2025–2026"
           maxlength="50"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
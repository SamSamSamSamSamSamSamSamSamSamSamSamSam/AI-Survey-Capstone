{{-- resources/views/admin/semesters/_form.blade.php --}}

<div class="mb-4">
    <label class="form-label" for="name">
        Display Name <span class="text-danger">*</span>
    </label>
    <p class="form-text mt-0 mb-1">A human-readable label for this semester.</p>
    <input type="text"
           name="name"
           id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $semester->name ?? '') }}"
           placeholder="e.g. 1st Semester 2024–2025"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="academic_start_year">
        Academic Year Start <span class="text-danger">*</span>
    </label>
    <p class="form-text mt-0 mb-1">The year the academic year begins (e.g. 2024 for A.Y. 2024–2025).</p>
    <input type="number"
           name="academic_start_year"
           id="academic_start_year"
           min="2000" max="2099"
           class="form-control @error('academic_start_year') is-invalid @enderror"
           value="{{ old('academic_start_year', $semester->academic_start_year ?? date('Y')) }}"
           style="max-width:160px;"
           placeholder="{{ date('Y') }}"
           required>
    @error('academic_start_year')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="semester_number">
        Semester <span class="text-danger">*</span>
    </label>
    <select name="semester_number"
            id="semester_number"
            class="form-select @error('semester_number') is-invalid @enderror"
            style="max-width:220px;"
            required>
        <option value="">Select semester…</option>
        <option value="1" @selected(old('semester_number', $semester->semester_number ?? '') == 1)>1st Semester</option>
        <option value="2" @selected(old('semester_number', $semester->semester_number ?? '') == 2)>2nd Semester</option>
        <option value="3" @selected(old('semester_number', $semester->semester_number ?? '') == 3)>Summer</option>
    </select>
    @error('semester_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
{{-- resources/views/admin/semesters/_form.blade.php --}}

<div class="form-group">
    <label class="form-label">Display Name <span style="color:#dc2626">*</span></label>
    <input type="text" name="name"
           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
           value="{{ old('name', $semester->name ?? '') }}"
           placeholder="e.g. 1st Semester 2024–2025">
    <p class="form-text">A human-readable label for this semester.</p>
    @error('name') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Academic Year Start <span style="color:#dc2626">*</span></label>
    <input type="number" name="academic_start_year" min="2000" max="2099"
           class="form-control {{ $errors->has('academic_start_year') ? 'is-invalid' : '' }}"
           value="{{ old('academic_start_year', $semester->academic_start_year ?? date('Y')) }}"
           style="max-width:140px;"
           placeholder="{{ date('Y') }}">
    <p class="form-text">The year the academic year begins (e.g. 2024 for A.Y. 2024–2025).</p>
    @error('academic_start_year') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Semester Number <span style="color:#dc2626">*</span></label>
    <select name="semester_number" class="form-control {{ $errors->has('semester_number') ? 'is-invalid' : '' }}" style="max-width:200px;">
        <option value="">Select…</option>
        <option value="1" @selected(old('semester_number', $semester->semester_number ?? '') == 1)>1st Semester</option>
        <option value="2" @selected(old('semester_number', $semester->semester_number ?? '') == 2)>2nd Semester</option>
        <option value="3" @selected(old('semester_number', $semester->semester_number ?? '') == 3)>Summer</option>
    </select>
    @error('semester_number') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

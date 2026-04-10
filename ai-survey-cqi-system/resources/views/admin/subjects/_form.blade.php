{{-- resources/views/admin/subjects/_form.blade.php --}}

<div class="form-group">
    <label class="form-label">Course Code <span style="color:#dc2626">*</span></label>
    <input type="text" name="course_code"
           class="form-control {{ $errors->has('course_code') ? 'is-invalid' : '' }}"
           value="{{ old('course_code', $subject->course_code ?? '') }}"
           placeholder="e.g. CS101">
    @error('course_code') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Subject Name <span style="color:#dc2626">*</span></label>
    <input type="text" name="name"
           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
           value="{{ old('name', $subject->name ?? '') }}"
           placeholder="e.g. Introduction to Programming">
    @error('name') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Units <span style="color:#dc2626">*</span></label>
    <input type="number" name="units" min="1" max="10"
           class="form-control {{ $errors->has('units') ? 'is-invalid' : '' }}"
           value="{{ old('units', $subject->units ?? '') }}"
           style="max-width:120px;">
    @error('units') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3"
              class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
              placeholder="Optional description…">{{ old('description', $subject->description ?? '') }}</textarea>
    @error('description') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

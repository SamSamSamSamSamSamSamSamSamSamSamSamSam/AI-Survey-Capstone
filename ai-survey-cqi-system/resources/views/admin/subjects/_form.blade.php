{{-- resources/views/admin/subjects/_form.blade.php --}}

<div class="mb-4">
    <label class="form-label" for="course_code">
        Course Code <span class="text-danger">*</span>
    </label>
    <p class="form-text mt-0 mb-1">Short uppercase identifier for the subject.</p>
    <input type="text"
           name="course_code"
           id="course_code"
           class="form-control @error('course_code') is-invalid @enderror"
           value="{{ old('course_code', $subject->course_code ?? '') }}"
           placeholder="e.g. CS101"
           style="text-transform:uppercase; max-width:200px;"
           required>
    @error('course_code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="name">
        Subject Name <span class="text-danger">*</span>
    </label>
    <input type="text"
           name="name"
           id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $subject->name ?? '') }}"
           placeholder="e.g. Introduction to Programming"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="units">
        Units <span class="text-danger">*</span>
    </label>
    <input type="number"
           name="units"
           id="units"
           min="1" max="10"
           class="form-control @error('units') is-invalid @enderror"
           value="{{ old('units', $subject->units ?? '') }}"
           style="max-width:120px;"
           required>
    @error('units')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="description">Description</label>
    <textarea name="description"
              id="description"
              rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Optional description…">{{ old('description', $subject->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
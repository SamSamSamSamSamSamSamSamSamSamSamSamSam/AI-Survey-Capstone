<div class="mb-4">
    <label for="name" class="form-label">
        Category Name <span class="text-danger">*</span>
    </label>
    <input type="text" name="name" id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $questionCategory->name ?? '') }}"
           placeholder="e.g. Teaching Effectiveness"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label for="description" class="form-label">
        Description
        <span class="form-label-optional">optional</span>
    </label>
    <textarea name="description" id="description" rows="3"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Optional description…">{{ old('description', $questionCategory->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

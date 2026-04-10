{{-- resources/views/admin/question-categories/_form.blade.php --}}
<div class="form-group">
    <label class="form-label">Category Name <span style="color:#dc2626">*</span></label>
    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
           value="{{ old('name', $questionCategory->name ?? '') }}"
           placeholder="e.g. Teaching Effectiveness">
    @error('name') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>
<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" rows="2" class="form-control"
              placeholder="Optional description…">{{ old('description', $questionCategory->description ?? '') }}</textarea>
</div>

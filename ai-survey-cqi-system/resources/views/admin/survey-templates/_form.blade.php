{{-- resources/views/admin/survey-templates/_form.blade.php --}}
<div class="form-group">
    <label class="form-label">Template Name <span style="color:#dc2626">*</span></label>
    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
           value="{{ old('name', $surveyTemplate->name ?? '') }}"
           placeholder="e.g. Official Faculty Evaluation Questionnaire">
    @error('name') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3" class="form-control"
              placeholder="Optional description of this template's purpose…">{{ old('description', $surveyTemplate->description ?? '') }}</textarea>
</div>

<div style="display:flex;gap:2rem;margin-top:.5rem;">
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;cursor:pointer;">
        <input type="hidden" name="is_official" value="0">
        <input type="checkbox" name="is_official" value="1"
               {{ old('is_official', $surveyTemplate->is_official ?? false) ? 'checked' : '' }}>
        <span><strong>Official Questionnaire</strong> <span style="display:block;font-size:.75rem;color:#6b7280;">Mark as the university's official instrument.</span></span>
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;cursor:pointer;">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $surveyTemplate->is_active ?? true) ? 'checked' : '' }}>
        <span><strong>Active</strong> <span style="display:block;font-size:.75rem;color:#6b7280;">Available for selection when creating surveys.</span></span>
    </label>
</div>

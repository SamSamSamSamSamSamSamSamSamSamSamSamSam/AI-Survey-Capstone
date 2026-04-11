{{-- ============================================================
     admin/survey-templates/_form.blade.php
     Shared by create.blade.php and edit.blade.php
     ============================================================ --}}

{{-- Template Name --}}
<div class="mb-4">
    <label class="form-label" for="name">
        Template Name <span class="text-danger">*</span>
    </label>
    <input type="text" name="name" id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $surveyTemplate->name ?? '') }}"
           placeholder="e.g. Official Faculty Evaluation Questionnaire"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Description --}}
<div class="mb-4">
    <label class="form-label" for="description">
        Description
        <span class="form-label-optional">optional</span>
    </label>
    <textarea name="description" id="description" rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Optional description of this template's purpose…">{{ old('description', $surveyTemplate->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Toggle options --}}
<div class="template-toggles">

    <label class="toggle-option">
        {{-- Hidden fallback so unchecked still submits 0 --}}
        <input type="hidden" name="is_official" value="0">
        <input type="checkbox" name="is_official" value="1"
               class="toggle-option__input"
               {{ old('is_official', $surveyTemplate->is_official ?? false) ? 'checked' : '' }}>
        <div class="toggle-option__body">
            <span class="toggle-option__icon toggle-option__icon--star">
                <i class="bi bi-star-fill"></i>
            </span>
            <div>
                <p class="toggle-option__title">Official Questionnaire</p>
                <p class="toggle-option__hint">
                    Mark as the university's official evaluation instrument.
                </p>
            </div>
        </div>
    </label>

    <label class="toggle-option">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
               class="toggle-option__input"
               {{ old('is_active', $surveyTemplate->is_active ?? true) ? 'checked' : '' }}>
        <div class="toggle-option__body">
            <span class="toggle-option__icon toggle-option__icon--active">
                <i class="bi bi-check-circle-fill"></i>
            </span>
            <div>
                <p class="toggle-option__title">Active</p>
                <p class="toggle-option__hint">
                    Make this template available when creating surveys.
                </p>
            </div>
        </div>
    </label>

</div>
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
<div class="row g-3 mb-4">
    {{-- Official Status --}}
    <div class="col-md-6">
        <div class="card h-100 border shadow-sm">
            <div class="card-body d-flex align-items-start">
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">Official Instrument</h6>
                    <p class="text-muted small mb-0">Designate as the university's primary evaluation standard.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input type="hidden" name="is_official" value="0">
                    <input class="form-check-input h5" type="checkbox" name="is_official" value="1" 
                           id="isOfficial" {{ old('is_official', $surveyTemplate->is_official ?? false) ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Status --}}
    <div class="col-md-6">
        <div class="card h-100 border shadow-sm">
            <div class="card-body d-flex align-items-start">
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">Availability</h6>
                    <p class="text-muted small mb-0">Enable this template for use in new surveys.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input h5" type="checkbox" name="is_active" value="1" 
                           id="isActive" {{ old('is_active', $surveyTemplate->is_active ?? true) ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>
</div>
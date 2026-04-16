{{-- resources/views/admin/curricula/_form.blade.php --}}

<div class="mb-4">
    <label class="form-label" for="program_id">
        Program <span class="text-danger">*</span>
    </label>
    <select name="program_id"
            id="program_id"
            class="form-select @error('program_id') is-invalid @enderror"
            required>
        <option value="">Select program…</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}"
                    @selected(old('program_id', $curriculum->program_id ?? '') == $program->id)>
                {{ $program->program_code }} — {{ $program->name }}
            </option>
        @endforeach
    </select>
    @error('program_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="curriculum_code">
        Curriculum Code <span class="text-danger">*</span>
    </label>
    <p class="form-text mt-0 mb-1">Must be unique within the selected program.</p>
    <input type="text"
           name="curriculum_code"
           id="curriculum_code"
           class="form-control @error('curriculum_code') is-invalid @enderror"
           value="{{ old('curriculum_code', $curriculum->curriculum_code ?? '') }}"
           placeholder="e.g. BSIT-2024"
           required>
    @error('curriculum_code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="description">Description</label>
    <input type="text"
           name="description"
           id="description"
           class="form-control @error('description') is-invalid @enderror"
           value="{{ old('description', $curriculum->description ?? '') }}"
           placeholder="Optional — e.g. Revised curriculum per CMO 2024">
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label" for="effective_year">
            Effective Year <span class="text-danger">*</span>
        </label>
        <input type="number"
               name="effective_year"
               id="effective_year"
               min="2000" max="2099"
               class="form-control @error('effective_year') is-invalid @enderror"
               value="{{ old('effective_year', $curriculum->effective_year ?? date('Y')) }}"
               required>
        @error('effective_year')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 d-flex flex-column justify-content-end">
        <div class="form-check form-switch mb-1" style="padding-top:.3rem;">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input"
                   type="checkbox"
                   name="is_active"
                   id="is_active"
                   value="1"
                   {{ old('is_active', $curriculum->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <p class="form-text mt-0">Mark as the current curriculum for this program.</p>
    </div>
</div>
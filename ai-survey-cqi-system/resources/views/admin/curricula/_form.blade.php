{{-- resources/views/admin/curricula/_form.blade.php --}}

<div class="form-group">
    <label class="form-label">Program <span style="color:#dc2626">*</span></label>
    <select name="program_id" class="form-control {{ $errors->has('program_id') ? 'is-invalid' : '' }}">
        <option value="">Select program…</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}" @selected(old('program_id', $curriculum->program_id ?? '') == $program->id)>
                {{ $program->program_code }} — {{ $program->name }}
            </option>
        @endforeach
    </select>
    @error('program_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Curriculum Code <span style="color:#dc2626">*</span></label>
    <input type="text" name="curriculum_code"
           class="form-control {{ $errors->has('curriculum_code') ? 'is-invalid' : '' }}"
           value="{{ old('curriculum_code', $curriculum->curriculum_code ?? '') }}"
           placeholder="e.g. BSIT-2024">
    <p class="form-text">Must be unique within the selected program.</p>
    @error('curriculum_code') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label class="form-label">Description</label>
    <input type="text" name="description"
           class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
           value="{{ old('description', $curriculum->description ?? '') }}"
           placeholder="Optional — e.g. Revised curriculum per CMO 2024">
    @error('description') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div style="display:flex;gap:1rem;">
    <div class="form-group" style="flex:1;">
        <label class="form-label">Effective Year <span style="color:#dc2626">*</span></label>
        <input type="number" name="effective_year" min="2000" max="2099"
               class="form-control {{ $errors->has('effective_year') ? 'is-invalid' : '' }}"
               value="{{ old('effective_year', $curriculum->effective_year ?? date('Y')) }}">
        @error('effective_year') <p class="invalid-feedback">{{ $message }}</p> @enderror
    </div>

    <div class="form-group" style="flex:1;">
        <label class="form-label">Status</label>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.6rem;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $curriculum->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" style="font-weight:400;font-size:.9rem;">Active</label>
        </div>
    </div>
</div>

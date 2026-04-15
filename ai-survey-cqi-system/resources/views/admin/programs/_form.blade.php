{{-- ============================================================
     admin/programs/_form.blade.php
     Shared by create.blade.php and edit.blade.php
     ============================================================ --}}

{{-- Program Code --}}
<div class="mb-4">
    <label class="form-label" for="program_code">
        Program Code <span class="text-danger">*</span>
    </label>
    <p class="form-text mt-0 mb-1">Short uppercase identifier for the program.</p>
    <input type="text"
           name="program_code"
           id="program_code"
           class="form-control @error('program_code') is-invalid @enderror"
           value="{{ old('program_code', $program->program_code ?? '') }}"
           placeholder="e.g. BSCS"
           style="text-transform: uppercase;"
           required>
    @error('program_code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Program Name --}}
<div class="mb-4">
    <label class="form-label" for="name">
        Program Name <span class="text-danger">*</span>
    </label>
    <input type="text"
           name="name"
           id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $program->name ?? '') }}"
           placeholder="e.g. Bachelor of Science in Computer Science"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
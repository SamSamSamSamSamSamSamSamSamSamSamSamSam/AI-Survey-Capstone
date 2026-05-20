@extends('layouts.app')
@section('title', 'Create School Year')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.semesters.index') }}">Semesters</a></li>
    <li class="breadcrumb-item active">Create School Year</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Create School Year</h2>
        <p class="page-subheading">
            Automatically generate semesters for a new academic year.
        </p>
    </div>
    <a href="{{ route('admin.semesters.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Semesters
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.semesters.store') }}" novalidate>
            @csrf

            {{-- Academic Start Year --}}
            <div class="mb-4">
                <label class="form-label" for="academic_start_year">
                    Academic Year <span class="text-danger">*</span>
                </label>
                <p class="form-text mt-0 mb-2">
                    Enter the <strong>starting year</strong> of the school year.
                    The system will generate semesters for
                    <span id="ay-preview" class="fw-semibold text-primary">
                        S.Y. {{ date('Y') }}–{{ date('Y') + 1 }}
                    </span>.
                </p>

                <div class="input-group" style="max-width:160px;">
                    <input type="number"
                           name="academic_start_year"
                           id="academic_start_year"
                           min="2000" max="2099"
                           class="form-control @error('academic_start_year') is-invalid @enderror"
                           value="{{ old('academic_start_year', date('Y')) }}"
                           required>

                    {{-- Vertical stepper buttons --}}
                    <div class="d-flex flex-column border rounded-end">
                        <button type="button"
                                class="btn btn-link btn-sm p-0 px-2 border-bottom text-dark"
                                onclick="stepYear(1)"
                                style="line-height:1;text-decoration:none;">
                            <i class="bi bi-chevron-up" style="font-size:.7rem;"></i>
                        </button>
                        <button type="button"
                                class="btn btn-link btn-sm p-0 px-2 text-dark"
                                onclick="stepYear(-1)"
                                style="line-height:1;text-decoration:none;">
                            <i class="bi bi-chevron-down" style="font-size:.7rem;"></i>
                        </button>
                    </div>
                </div>

                @error('academic_start_year')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Semesters to generate --}}
            <div class="mb-4">
                <label class="form-label">Semesters to Generate</label>
                <p class="form-text mt-0 mb-2">1st and 2nd Semester are always created. Summer is optional.</p>

                <div class="d-flex flex-column gap-2" style="max-width:280px;">

                    {{-- 1st Semester — always checked, disabled --}}
                    <label class="d-flex align-items-center gap-2 p-2 border rounded bg-light text-muted"
                           style="cursor:default; font-size:.875rem;">
                        <input type="checkbox" class="form-check-input" checked disabled>
                        <span>1st Semester</span>
                        <span class="badge bg-secondary ms-auto" style="font-size:.65rem;">Always</span>
                    </label>

                    {{-- 2nd Semester — always checked, disabled --}}
                    <label class="d-flex align-items-center gap-2 p-2 border rounded bg-light text-muted"
                           style="cursor:default; font-size:.875rem;">
                        <input type="checkbox" class="form-check-input" checked disabled>
                        <span>2nd Semester</span>
                        <span class="badge bg-secondary ms-auto" style="font-size:.65rem;">Always</span>
                    </label>

                    {{-- Summer — optional --}}
                    <label class="d-flex align-items-center gap-2 p-2 border rounded"
                           style="cursor:pointer; font-size:.875rem;"
                           for="include_summer">
                        <input type="checkbox"
                               name="include_summer"
                               id="include_summer"
                               class="form-check-input"
                               value="1"
                               {{ old('include_summer') ? 'checked' : '' }}>
                        <span>Summer <span class="text-muted">(optional)</span></span>
                    </label>

                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-calendar-plus me-1"></i> Generate Semesters
                </button>
                <a href="{{ route('admin.semesters.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function stepYear(delta) {
    const input   = document.getElementById('academic_start_year');
    const preview = document.getElementById('ay-preview');
    let val       = parseInt(input.value, 10) || {{ date('Y') }};
    val           = Math.min(2099, Math.max(2000, val + delta));
    input.value   = val;
    preview.textContent = `S.Y. ${val}–${val + 1}`;
}

document.getElementById('academic_start_year').addEventListener('input', function () {
    const val     = parseInt(this.value, 10);
    const preview = document.getElementById('ay-preview');
    if (val >= 2000 && val <= 2099) {
        preview.textContent = `S.Y. ${val}–${val + 1}`;
    }
});
</script>
@endpush
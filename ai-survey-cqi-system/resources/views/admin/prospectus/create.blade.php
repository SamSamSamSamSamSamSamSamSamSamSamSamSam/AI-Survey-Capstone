@extends('layouts.app')
@section('title', 'Add Prospectus Entry')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.prospectus.index') }}">Prospectus</a></li>
    <li class="breadcrumb-item active">Add Entry</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Add Prospectus Entry</h2>
        <p class="page-subheading">Assign a subject to a curriculum, year level, and semester.</p>
    </div>
    <a href="{{ route('admin.prospectus.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Prospectus
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.prospectus.store') }}" novalidate>
            @csrf

            {{-- Program --}}
            <div class="mb-4">
                <label class="form-label" for="program_id">
                    Program <span class="text-danger">*</span>
                </label>
                <select name="program_id" id="program_id"
                        class="form-select @error('program_id') is-invalid @enderror">
                    <option value="">Select program…</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}"
                            @selected(old('program_id', request('program_id')) == $program->id)>
                            {{ $program->program_code }} — {{ $program->name }}
                        </option>
                    @endforeach
                </select>
                @error('program_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Curriculum --}}
            <div class="mb-4">
                <label class="form-label" for="curriculum_id">
                    Curriculum <span class="text-danger">*</span>
                </label>
                <select name="curriculum_id" id="curriculum_id"
                        class="form-select @error('curriculum_id') is-invalid @enderror">
                    <option value="">Select curriculum…</option>
                    @foreach ($curricula as $c)
                        <option value="{{ $c->id }}"
                            @selected(old('curriculum_id', request('curriculum_id')) == $c->id)>
                            {{ $c->display_label }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Select a program first to load its curricula.</div>
                @error('curriculum_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Subject --}}
            <div class="mb-4">
                <label class="form-label" for="subject_id">
                    Subject <span class="text-danger">*</span>
                </label>
                <select name="subject_id" id="subject_id"
                        class="form-select @error('subject_id') is-invalid @enderror">
                    <option value="">Select subject…</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}"
                            @selected(old('subject_id') == $subject->id)>
                            {{ $subject->course_code }} — {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Year Level + Semester --}}
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label" for="year_level">
                        Year Level <span class="text-danger">*</span>
                    </label>
                    <select name="year_level" id="year_level"
                            class="form-select @error('year_level') is-invalid @enderror">
                        <option value="">Select…</option>
                        @foreach (range(1, 5) as $y)
                            <option value="{{ $y }}" @selected(old('year_level') == $y)>
                                Year {{ $y }}
                            </option>
                        @endforeach
                    </select>
                    @error('year_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label" for="semester_id">
                        Semester <span class="text-danger">*</span>
                    </label>
                    <select name="semester_id" id="semester_id"
                            class="form-select @error('semester_id') is-invalid @enderror">
                        <option value="">Select…</option>
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}"
                                @selected(old('semester_id') == $semester->id)>
                                {{ $semester->full_label }}
                            </option>
                        @endforeach
                    </select>
                    @error('semester_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add to Prospectus
                </button>
                <a href="{{ route('admin.prospectus.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const programSelect   = document.getElementById('program_id');
    const curriculumSelect = document.getElementById('curriculum_id');

    if (!programSelect || !curriculumSelect) return;

    programSelect.addEventListener('change', function () {
        const programId = this.value;
        curriculumSelect.innerHTML = '<option value="">Loading…</option>';
        curriculumSelect.disabled = true;

        if (!programId) {
            curriculumSelect.innerHTML = '<option value="">Select curriculum…</option>';
            curriculumSelect.disabled = false;
            return;
        }

        fetch(`/admin/curricula/by-program/${programId}`)
            .then(res => res.json())
            .then(data => {
                curriculumSelect.innerHTML = '<option value="">Select curriculum…</option>';
                data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value       = c.id;
                    opt.textContent = c.display_label + (c.is_active ? ' · Active' : '');
                    curriculumSelect.appendChild(opt);
                });
                curriculumSelect.disabled = false;
            })
            .catch(() => {
                curriculumSelect.innerHTML = '<option value="">Error loading curricula</option>';
                curriculumSelect.disabled = false;
            });
    });
})();
</script>
@endpush
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
        <p class="page-subheading">Assign a subject to a specific year level and semester in a curriculum.</p>
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
                <select name="program_id"
                        id="program_id"
                        class="form-select @error('program_id') is-invalid @enderror"
                        required>
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
                <select name="curriculum_id"
                        id="curriculum_id"
                        class="form-select @error('curriculum_id') is-invalid @enderror"
                        required>
                    <option value="">Select curriculum…</option>
                    @foreach ($curricula as $c)
                        <option value="{{ $c->id }}"
                                @selected(old('curriculum_id', request('curriculum_id')) == $c->id)>
                            {{ $c->display_label }}{{ $c->is_active ? ' (Active)' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="form-text">Select a program first to load its available curricula.</p>
                @error('curriculum_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Subject --}}
            <div class="mb-4">
                <label class="form-label" for="subject_id">
                    Subject <span class="text-danger">*</span>
                </label>
                <select name="subject_id"
                        id="subject_id"
                        class="form-select @error('subject_id') is-invalid @enderror"
                        required>
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
                <div class="col-md-6">
                    <label class="form-label" for="year_level">
                        Year Level <span class="text-danger">*</span>
                    </label>
                    <select name="year_level"
                            id="year_level"
                            class="form-select @error('year_level') is-invalid @enderror"
                            required>
                        <option value="">Select year…</option>
                        @foreach (range(1, 5) as $y)
                            <option value="{{ $y }}" @selected(old('year_level') == $y)>Year {{ $y }}</option>
                        @endforeach
                    </select>
                    @error('year_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="semester_number">
                        Semester <span class="text-danger">*</span>
                    </label>
                    <select name="semester_number"
                            id="semester_number"
                            class="form-select @error('semester_number') is-invalid @enderror"
                            required>
                        <option value="">Select semester…</option>
                        <option value="1" @selected(old('semester_number') == 1)>1st Semester</option>
                        <option value="2" @selected(old('semester_number') == 2)>2nd Semester</option>
                        <option value="3" @selected(old('semester_number') == 3)>Summer</option>
                    </select>
                    @error('semester_number')
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
<script src="{{ asset('js/pages/prospectus-create.js') }}"></script>
@endpush
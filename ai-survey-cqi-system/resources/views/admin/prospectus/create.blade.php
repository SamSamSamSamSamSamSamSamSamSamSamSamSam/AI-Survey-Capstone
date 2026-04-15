@extends('admin.layouts.app')
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
    <h1>Add Prospectus Entry</h1>
    <a href="{{ route('admin.prospectus.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.prospectus.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Program <span style="color:#dc2626">*</span></label>
                <select name="program_id" id="program_id"
                        class="form-control {{ $errors->has('program_id') ? 'is-invalid' : '' }}">
                    <option value="">Select program…</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}"
                                @selected(old('program_id', request('program_id')) == $program->id)>
                            {{ $program->program_code }} — {{ $program->name }}
                        </option>
                    @endforeach
                </select>
                @error('program_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Curriculum <span style="color:#dc2626">*</span></label>
                <select name="curriculum_id" id="curriculum_id"
                        class="form-control {{ $errors->has('curriculum_id') ? 'is-invalid' : '' }}">
                    <option value="">Select curriculum…</option>
                    {{-- Pre-populated if arriving from a program context --}}
                    @foreach ($curricula as $c)
                        <option value="{{ $c->id }}" @selected(old('curriculum_id', request('curriculum_id')) == $c->id)>
                            {{ $c->display_label }}
                        </option>
                    @endforeach
                </select>
                <p class="form-text">Select a program first to load its curricula.</p>
                @error('curriculum_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Subject <span style="color:#dc2626">*</span></label>
                <select name="subject_id"
                        class="form-control {{ $errors->has('subject_id') ? 'is-invalid' : '' }}">
                    <option value="">Select subject…</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                            {{ $subject->course_code }} — {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                @error('subject_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div style="display:flex;gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Year Level <span style="color:#dc2626">*</span></label>
                    <select name="year_level"
                            class="form-control {{ $errors->has('year_level') ? 'is-invalid' : '' }}">
                        <option value="">Select…</option>
                        @foreach (range(1, 5) as $y)
                            <option value="{{ $y }}" @selected(old('year_level') == $y)>Year {{ $y }}</option>
                        @endforeach
                    </select>
                    @error('year_level') <p class="invalid-feedback">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="flex:1;">
                    <label class="form-label">Semester <span style="color:#dc2626">*</span></label>
                    <select name="semester_number"
                            class="form-control {{ $errors->has('semester_number') ? 'is-invalid' : '' }}">
                        <option value="">Select…</option>
                        <option value="1" @selected(old('semester_number') == 1)>1st Semester</option>
                        <option value="2" @selected(old('semester_number') == 2)>2nd Semester</option>
                        <option value="3" @selected(old('semester_number') == 3)>Summer</option>
                    </select>
                    @error('semester_number') <p class="invalid-feedback">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Add to Prospectus</button>
                <a href="{{ route('admin.prospectus.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Dynamic curriculum loader --}}
<script>
document.getElementById('program_id').addEventListener('change', function () {
    const programId   = this.value;
    const curriculumSelect = document.getElementById('curriculum_id');

    curriculumSelect.innerHTML = '<option value="">Loading…</option>';

    if (! programId) {
        curriculumSelect.innerHTML = '<option value="">Select curriculum…</option>';
        return;
    }

    fetch(`/admin/curricula/by-program/${programId}`)
        .then(res => res.json())
        .then(data => {
            curriculumSelect.innerHTML = '<option value="">Select curriculum…</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value       = c.id;
                opt.textContent = c.display_label + (c.is_active ? ' (Active)' : '');
                curriculumSelect.appendChild(opt);
            });
        })
        .catch(() => {
            curriculumSelect.innerHTML = '<option value="">Error loading curricula</option>';
        });
});
</script>
@endsection

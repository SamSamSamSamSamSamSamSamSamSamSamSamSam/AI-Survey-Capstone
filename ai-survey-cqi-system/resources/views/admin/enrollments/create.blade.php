@extends('admin.layouts.app')
@section('title', 'Enroll Student')

@section('content')
<div class="page-header">
    <h1>Enroll Student</h1>
    <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-secondary">← Back</a>
</div>

<div class="alert alert-info" style="font-size:.875rem;margin-bottom:1.25rem;">
    Enrolling into: <strong>{{ $offering->subject->course_code }} — {{ $offering->subject->name }}</strong>
    &nbsp;·&nbsp; {{ $offering->semester->full_label }}
    &nbsp;·&nbsp; Faculty: {{ $offering->teacher->name }}
</div>

<div class="card" style="max-width:520px;">
    <div class="card-body">

        @if ($students->isEmpty())
            <p style="color:#6b7280;font-size:.9rem;">All available students are already enrolled in this offering.</p>
        @else
            <form method="POST" action="{{ route('admin.offerings.enrollments.store', $offering->id) }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Student <span style="color:#dc2626">*</span></label>
                    <select name="student_id" class="form-control {{ $errors->has('student_id') ? 'is-invalid' : '' }}">
                        <option value="">Select student…</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->user_id_number }} — {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Student Status <span style="color:#dc2626">*</span></label>
                    <select name="student_status_id" class="form-control {{ $errors->has('student_status_id') ? 'is-invalid' : '' }}">
                        <option value="">Select status…</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" @selected(old('student_status_id') == $status->id)>
                                {{ ucfirst($status->name) }}
                                @if ($status->description) — {{ $status->description }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('student_status_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
                </div>

                <div class="actions" style="margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">Enroll Student</button>
                    <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        @endif

    </div>
</div>
@endsection

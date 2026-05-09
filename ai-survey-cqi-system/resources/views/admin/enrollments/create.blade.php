@extends('layouts.app')
@section('title', 'Enroll Student')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.show', $offering->id) }}">Offerings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}">Enrollments</a></li>
    <li class="breadcrumb-item active">Enroll Student</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Enroll Student</h2>
        <p class="page-subheading d-flex align-items-center gap-2 flex-wrap">
            <span class="program-code-badge">{{ $offering->subject->course_code }}</span>
            {{ $offering->subject->name }}
        </p>
    </div>
    <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Enrollments
    </a>
</div>

{{-- Offering context strip --}}
<div class="attempts-meta-strip mb-4">
    <div class="attempts-meta-strip__item">
        <i class="bi bi-calendar3 me-1"></i>
        {{ $offering->semester->full_label }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-person-badge me-1"></i>
        {{ $offering->teacher->name }}
    </div>
</div>

<div class="form-page-layout">
    <div class="form-card">

        @if ($students->isEmpty())
            <div class="empty-state" style="padding:2rem 0;">
                <div class="empty-state-icon"><i class="bi bi-person-check"></i></div>
                <p class="empty-state-text">All available students are already enrolled in this offering.</p>
                <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Enrollments
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.offerings.enrollments.store', $offering->id) }}" novalidate>
                @csrf

                {{-- Multiple Student Selection --}}
                <div class="mb-4">
                    <label class="form-label" for="student_id">
                        Students <span class="text-danger">*</span>
                    </label>
                    <select name="student_id[]"
                            id="student-select-multiple"
                            class="form-select @error('student_id') is-invalid @enderror"
                            multiple
                            placeholder="Search and select students..."
                            required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(in_array($student->id, (array)old('student_id')))>
                                {{ $student->user_id_number }} — {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status selection (applied to all selected students) --}}
                <div class="mb-4">
                    <label class="form-label" for="enrollment_type_id">
                        Enrollment Type <span class="text-danger">*</span>
                    </label>
                    <select name="enrollment_type_id"
                            id="enrollment_type_id"
                            class="form-select @error('enrollment_type_id') is-invalid @enderror"
                            >
                        <option value="">Select status for these students…</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" @selected(old('enrollment_type_id') == $status->id)>
                                {{ ucfirst($status->name) }}@if($status->description) — {{ $status->description }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('enrollment_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Enroll Students
                    </button>
                    <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        @endif

    </div>
</div>

@endsection

@push('scripts')
    <script>
    // Wait for Vite to finish loading app.js/TomSelect
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('student-select-multiple')) {
                new TomSelect("#student-select-multiple", {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                    onItemAdd: function() {
                        this.setTextboxValue('');
                        this.refreshOptions();
                    }
                });
            }
        });
    </script>
@endpush
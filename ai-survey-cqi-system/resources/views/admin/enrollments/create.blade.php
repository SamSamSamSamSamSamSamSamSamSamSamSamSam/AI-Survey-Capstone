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

                <div class="mb-4">
                    <label class="form-label" for="student_id">
                        Student <span class="text-danger">*</span>
                    </label>
                    <select name="student_id"
                            id="student_id"
                            class="form-select @error('student_id') is-invalid @enderror"
                            required>
                        <option value="">Select student…</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->user_id_number }} — {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="student_status_id">
                        Student Status <span class="text-danger">*</span>
                    </label>
                    <select name="student_status_id"
                            id="student_status_id"
                            class="form-select @error('student_status_id') is-invalid @enderror"
                            required>
                        <option value="">Select status…</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" @selected(old('student_status_id') == $status->id)>
                                {{ ucfirst($status->name) }}@if($status->description) — {{ $status->description }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('student_status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Enroll Student
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
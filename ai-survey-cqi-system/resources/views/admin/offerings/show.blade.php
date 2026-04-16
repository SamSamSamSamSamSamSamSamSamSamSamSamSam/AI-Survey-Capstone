@extends('layouts.app')
@section('title', $offering->display_name)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.index') }}">Course Offerings</a></li>
    <li class="breadcrumb-item active">{{ $offering->display_name }}</li>
</ol>
@endsection

@section('content')

{{-- ===== HEADER ===== --}}
<div class="page-header flex-wrap gap-2">
    <div class="d-flex align-items-start gap-3">
        <div class="program-icon program-icon--lg">
            <i class="bi bi-easel-fill"></i>
        </div>
        <div>
            <h2 class="page-heading">{{ $offering->display_name }}</h2>
            <p class="page-subheading">
                {{ $offering->subject->name }} ·
                {{ $offering->semester->full_label }}
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.offerings.edit', $offering->id) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}"
           class="btn btn-primary btn-sm">
            <i class="bi bi-people me-1"></i> Manage Enrollments
        </a>
        <a href="{{ route('admin.offerings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== META + STATS GRID ===== --}}
<div class="offering-show-grid mb-4">

    {{-- Details --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="detail-row">
                <span class="detail-label">
                    <i class="bi bi-book me-2 text-muted"></i>Subject
                </span>
                <span class="detail-value">
                    <span class="program-code-badge program-code-badge--subject me-1">
                        {{ $offering->subject->course_code }}
                    </span>
                    {{ $offering->subject->name }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">
                    <i class="bi bi-calendar3 me-2 text-muted"></i>Semester
                </span>
                <span class="detail-value">{{ $offering->semester->full_label }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">
                    <i class="bi bi-person-workspace me-2 text-muted"></i>Faculty
                </span>
                <span class="detail-value">
                    <div class="user-cell">
                        <div class="user-avatar-sm">
                            {{ strtoupper(substr($offering->teacher->name, 0, 2)) }}
                        </div>
                        {{ $offering->teacher->name }}
                    </div>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">
                    <i class="bi bi-tag me-2 text-muted"></i>Type
                </span>
                <span class="detail-value">
                    @if ($offering->offeringType)
                        <span class="category-tag">{{ $offering->offeringType->name }}</span>
                    @else
                        <span class="text-muted-sm">—</span>
                    @endif
                </span>
            </div>
            <div class="detail-row detail-row--last">
                <span class="detail-label">
                    <i class="bi bi-people me-2 text-muted"></i>Group
                </span>
                <span class="detail-value">
                    {{ $offering->group_number ? 'Group ' . $offering->group_number : '—' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Enrollment summary --}}
    <div class="card">
        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center gap-2">
            <div class="kpi-icon kpi-icon--success" style="width:52px;height:52px;font-size:1.3rem;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="survey-stat__value" style="font-size:2.5rem;">
                {{ $offering->enrollments->count() }}
            </div>
            <div class="survey-stat__label">Total Students Enrolled</div>
            <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}"
               class="btn btn-primary btn-sm mt-2">
                <i class="bi bi-person-plus me-1"></i> Enroll Student
            </a>
        </div>
    </div>

</div>

{{-- ===== ENROLLED STUDENTS ===== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">
        Enrolled Students
        <span class="ms-2 count-badge count-badge--responses">
            {{ $offering->enrollments->count() }}
        </span>
    </p>
    <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}"
       class="btn btn-sm btn-primary">
        <i class="bi bi-person-plus me-1"></i> Enroll Student
    </a>
</div>

<div class="card">
    @if ($offering->enrollments->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-people"></i></div>
            <p class="empty-state-text">No students enrolled yet.</p>
            <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i> Enroll First Student
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Enrollment Type</th>
                        <th>Enrolled On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($offering->enrollments as $enrollment)
                    <tr>
                        <td class="text-mono">{{ $enrollment->student->user_id_number }}</td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-sm">
                                    {{ strtoupper(substr($enrollment->student->name, 0, 2)) }}
                                </div>
                                <span class="fw-500">{{ $enrollment->student->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="role-pill {{ $enrollment->enrollmentType->name === 'Block-Enrolled' ? 'role-pill--faculty' : 'role-pill--student' }}">
                                {{ $enrollment->enrollmentType->name }}
                            </span>
                        </td>
                        <td class="text-muted-sm">{{ $enrollment->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <form method="POST"
                                  action="{{ route('admin.offerings.enrollments.destroy', [$offering->id, $enrollment->id]) }}"
                                  class="d-inline"
                                  data-confirm="Remove {{ $enrollment->student->name }} from this offering?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-icon--danger"
                                        title="Remove Student">
                                    <i class="bi bi-person-dash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
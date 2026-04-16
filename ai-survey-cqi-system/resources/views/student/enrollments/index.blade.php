@extends('layouts.app')
@section('title', 'My Enrollments')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Enrollments</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">My Enrollments</h2>
        <p class="page-subheading">Enroll in courses and manage your enrollment history.</p>
    </div>
</div>

{{-- ===== ENROLL IN A COURSE ===== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">
        <i class="bi bi-plus-circle me-2 text-muted"></i>Enroll in a Course
    </p>
</div>

@if (! $activeSemester)
    <div class="info-notice info-notice--warning mb-4">
        <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
        <div>
            Enrollment is currently unavailable. No active semester has been set by the administrator.
        </div>
    </div>

@elseif ($availableOfferings->isEmpty())
    <div class="card mb-4">
        <div class="empty-state">
            <div class="empty-state-icon" style="background: rgba(34,197,94,.1); color: #16a34a;">
                <i class="bi bi-check-circle"></i>
            </div>
            <p class="empty-state-text">
                You are already enrolled in all available courses for
                <strong>{{ $activeSemester->full_label }}</strong>.
            </p>
        </div>
    </div>

@else
    <div class="survey-banner survey-banner--active mb-3">
        <i class="bi bi-calendar-check-fill"></i>
        Enrolling for: <strong>{{ $activeSemester->full_label }}</strong>
    </div>

    <div class="enrollment-offering-grid mb-4">
        @foreach ($availableOfferings as $offering)
        <div class="enrollment-offering-card">
            <div class="enrollment-offering-card__code">
                {{ $offering->subject->course_code }}
            </div>
            <div class="enrollment-offering-card__name">
                {{ $offering->subject->name }}
            </div>
            @if ($offering->offeringType)
                <span class="role-pill role-pill--faculty mb-2">{{ $offering->offeringType->name }}</span>
            @endif
            <div class="enrollment-offering-card__meta">
                <div><i class="bi bi-person me-1"></i>{{ $offering->teacher->name }}</div>
                <div><i class="bi bi-journal me-1"></i>{{ $offering->subject->units }} unit(s)</div>
                @if ($offering->group_number)
                    <div><i class="bi bi-people me-1"></i>Group {{ $offering->group_number }}</div>
                @endif
            </div>
            <form method="POST" action="{{ route('student.enrollments.store') }}">
                @csrf
                <input type="hidden" name="offering_id" value="{{ $offering->id }}">
                <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                    <i class="bi bi-plus-lg me-1"></i> Enroll
                </button>
            </form>
        </div>
        @endforeach
    </div>
@endif

{{-- ===== ENROLLMENT HISTORY ===== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">
        <i class="bi bi-clock-history me-2 text-muted"></i>My Enrollment History
    </p>
</div>

<div class="card">
    @if ($myEnrollments->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
            <p class="empty-state-text">No enrollment records yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Faculty</th>
                        <th>Semester</th>
                        <th>Type</th>
                        <th>Enrolled On</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($myEnrollments as $enrollment)
                    <tr>
                        <td>
                            <div class="fw-500 text-mono" style="font-size:.8rem;">
                                {{ $enrollment->offering->subject->course_code }}
                            </div>
                            <div class="text-muted-sm">
                                {{ Str::limit($enrollment->offering->subject->name, 32) }}
                            </div>
                        </td>
                        <td class="text-muted-sm">{{ $enrollment->offering->teacher->name }}</td>
                        <td class="text-muted-sm">{{ $enrollment->offering->semester->full_label }}</td>
                        <td>
                            @if ($enrollment->enrollmentType)
                                <span class="role-pill {{ $enrollment->enrollmentType->name === 'Block-Enrolled' ? 'role-pill--faculty' : 'role-pill--student' }}">
                                    {{ $enrollment->enrollmentType->name === 'Block-Enrolled' ? 'Block' : 'Individual' }}
                                </span>
                            @endif
                        </td>
                        <td class="text-muted-sm">{{ $enrollment->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            @if ($activeSemester && $enrollment->offering->semester_id === $activeSemester->id)
                                <form method="POST"
                                      action="{{ route('student.enrollments.destroy', $enrollment->id) }}"
                                      class="d-inline"
                                      data-confirm="Drop this course? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-icon--danger"
                                            title="Drop Course">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted-sm">Completed</span>
                            @endif
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
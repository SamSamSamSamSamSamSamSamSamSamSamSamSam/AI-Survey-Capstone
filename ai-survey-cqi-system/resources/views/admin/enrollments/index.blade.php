@extends('layouts.app')
@section('title', 'Enrollments')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.show', $offering->id) }}">Offerings</a></li>
    <li class="breadcrumb-item active">Enrollments</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Enrollments</h2>
        <p class="page-subheading d-flex align-items-center gap-2 flex-wrap">
            <span class="program-code-badge">{{ $offering->subject->course_code }}</span>
            {{ $offering->subject->name }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Enroll Student
        </a>
        <a href="{{ route('admin.offerings.index', $offering->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Offering
        </a>
    </div>
</div>

{{-- Offering context strip --}}
<div class="attempts-meta-strip mb-3">
    <div class="attempts-meta-strip__item">
        <i class="bi bi-calendar3 me-1"></i>
        {{ $offering->semester->full_label }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-person-badge me-1"></i>
        {{ $offering->teacher->name }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-people me-1"></i>
        <strong>{{ $enrollments->total() }}</strong>&nbsp;{{ Str::plural('student', $enrollments->total()) }} enrolled
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-people me-1"></i>
         Group - {{ $offering->group_number }}
    </div>
</div>

{{-- Table --}}
<div class="card">
    @if ($enrollments->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-people"></i></div>
            <p class="empty-state-text">No students enrolled yet.</p>
            <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}" class="btn btn-primary btn-sm">
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
                        <th>Email</th>
                        <th>Status</th>
                        <th>Enrolled On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollments as $enrollment)
                    <tr>
                        <td>
                            <span class="program-code-badge">{{ $enrollment->student->user_id_number }}</span>
                        </td>
                        <td class="fw-500">{{ $enrollment->student->name }}</td>
                        <td style="font-size:.82rem;color:#6b7280;">{{ $enrollment->student->email }}</td>
                        <td>
                            <span class="status-pill status-pill--active">
                                <i class="bi bi-check-circle me-1"></i>{{ ucfirst($enrollment->enrollmentType->name) }}
                            </span>
                        </td>
                        <td style="font-size:.82rem;color:#6b7280;">
                            {{ $enrollment->created_at->format('M d, Y') }}
                        </td>
                        <td class="text-end">
                            <div class="table-actions">
                                <form method="POST"
                                      action="{{ route('admin.offerings.enrollments.destroy', [$offering->id, $enrollment->id]) }}"
                                      class="d-inline"
                                      data-confirm="Remove {{ $enrollment->student->name }} from this offering?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Remove">
                                        <i class="bi bi-person-dash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="table-pagination">{{ $enrollments->links() }}</div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
{{-- <script src="{{ asset('js/modules/confirm-action.js') }}"></script> --}}
@endpush
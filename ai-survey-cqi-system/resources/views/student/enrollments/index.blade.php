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
    {{-- search --}}
    <div class="row mb-3">
        <div class="col-md-6"> {{-- This makes it half-width on tablets/desktops --}}
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by course code, name, or teacher...">
            </div>
        </div>
    </div>

    {{-- dynamic container --}}
    <div id="offerings-container">
        @include('student.enrollments._offering_cards')
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
                                {{ $enrollment->offering->subject->course_code }} - Group {{$enrollment->offering->group_number}}
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
{{-- <script src="{{ asset('js/modules/confirm-action.js') }}"></script> --}}
    <script>
document.addEventListener("DOMContentLoaded", function () {
    let timer;
    const input = document.getElementById("searchInput");
    const container = document.getElementById("offerings-container");

    // Helper function to fetch data
    const fetchOfferings = (url) => {
        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(res => res.json())
        .then(data => {
            container.innerHTML = data.html;
        })
        .catch(err => console.error("Search Error:", err));
    };

    // Search Input Logic
    input.addEventListener("keyup", function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            // We always go back to page 1 when a new search starts
            fetchOfferings(`?search=${encodeURIComponent(input.value)}`);
        }, 400);
    });

    // Pagination Click Logic (using Event Delegation)
    document.addEventListener("click", function (e) {
        const link = e.target.closest(".pagination a");
        if (link) {
            e.preventDefault();
            fetchOfferings(link.href);
        }
    });
});
    </script>
@endpush
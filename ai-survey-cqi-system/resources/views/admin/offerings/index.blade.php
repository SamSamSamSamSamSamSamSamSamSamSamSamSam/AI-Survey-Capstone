@extends('layouts.app')
@section('title', 'Course Offerings')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Course Offerings</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Academic Offerings</h2>
        <p class="page-subheading">Manage course-faculty assignments per semester.</p>
    </div>
    <a href="{{ route('admin.offerings.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Offering
    </a>
</div>

@if (! $activeSemester)
    <div class="info-notice info-notice--warning mb-3">
        <i class="bi bi-exclamation-triangle-fill info-notice__icon"></i>
        <div>
            No active semester is set — showing all offerings.
            <a href="{{ route('admin.semesters.index') }}" class="fw-600 ms-1">
                Manage Semesters →
            </a>
        </div>
    </div>
@endif

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('admin.offerings.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select filter-input">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                                {{ $sem->full_label }}{{ $sem->is_active ? ' · Active' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select filter-input">
                        <option value="">All Subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>
                                {{ $subject->course_code }} - {{ Str::limit($subject->name, 25) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Faculty</label>
                    <select name="teacher_id" class="form-select filter-input">
                        <option value="">All Faculty</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select filter-input">
                        <option value="">Active</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all"     @selected(request('status') === 'all')>All</option>
                    </select>
                </div>


                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <div class="input-icon-wrap flex-grow-1">
                            <i class="bi bi-search input-icon"></i>
                            <input type="text" name="search" id="searchInput"
                                class="form-control auth-input"
                                placeholder="Search dynamically by subject code, title, or faculty name…"
                                value="{{ request('search') }}" autocomplete="off">
                        </div>
                        <a href="{{ route('admin.offerings.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE CONTAINER FOR AJAX UPDATE ===== --}}
<div id="tableContainer">
    @fragment('offerings-table')
    <div class="card">
        @if ($offerings->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-easel"></i></div>
                <p class="empty-state-text">No course offerings found matching the filters.</p>
                <a href="{{ route('admin.offerings.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Create First Offering
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Faculty</th>
                            <th>Semester</th>
                            <th class="text-center">Group</th>
                            <th class="text-center">Enrolled</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offerings as $offering)
                        <tr class="{{ $offering->trashed() ? 'row-muted' : '' }}">

                            <td>
                                <div>
                                    {{ $offering->subject->course_code }} - Group {{$offering->group_number}}
                                </div>
                                <div class="text-muted-sm">
                                    {{ Str::limit($offering->subject->name, 30) }}
                                </div>
                            </td>

                            <td>
                                <div class="user-cell">
                                    <span class="fw-500" style="font-size:.845rem;">
                                        {{ $offering->teacher->name }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-muted-sm">{{ $offering->semester->full_label }}</td>

                            <td class="text-center">
                                @if ($offering->group_number)
                                    <span class="count-badge">{{ $offering->group_number }}</span>
                                @else
                                    <span class="text-muted-sm">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="count-badge count-badge--responses">
                                    {{ $offering->enrollments_count ?? $offering->enrollments()->count() }}
                                </span>
                            </td>

                            <td>
                                @if ($offering->trashed())
                                    <span class="status-pill status-pill--archived">
                                        <i class="bi bi-archive me-1"></i>Archived
                                    </span>
                                @else
                                    <span class="status-pill status-pill--active">
                                        <i class="bi bi-check-circle me-1"></i>Active
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="table-actions">
                                    

                                    @if (! $offering->trashed())
                                        <a href="{{ route('admin.offerings.show', $offering->id) }}"
                                        class="btn btn-sm btn-icon" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.offerings.edit', $offering->id) }}"
                                           class="btn btn-sm btn-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}"
                                           class="btn btn-sm btn-icon" title="Manage Enrollments">
                                            <i class="bi bi-people"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.offerings.destroy', $offering->id) }}"
                                              class="d-inline"
                                              data-confirm="Archive this course offering?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-icon--danger"
                                                    title="Archive">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.offerings.restore', $offering->id) }}"
                                              class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-icon btn-icon--success"
                                                    title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($offerings->hasPages())
                <div class="table-pagination">{{ $offerings->links() }}</div>
            @endif
        @endif
    </div>
    @endfragment
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const tableContainer = document.getElementById('tableContainer');
    let debounceTimer;

    function fetchFilteredData() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const requestUrl = `${filterForm.action}?${params}`;

        fetch(requestUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            tableContainer.innerHTML = data.html;
            history.pushState(null, '', requestUrl);
        })
        .catch(error => console.error('Error fetching filtered data:', error));
    }

    filterForm.querySelectorAll('.filter-input').forEach(element => {
        element.addEventListener('change', fetchFilteredData);
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchFilteredData, 300);
    });

    // Handle back/forward browser history correctly
    window.addEventListener('popstate', function() {
        window.location.reload();
    });
});
</script>
@endpush
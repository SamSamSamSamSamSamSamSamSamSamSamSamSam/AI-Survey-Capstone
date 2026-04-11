@extends('layouts.app')
@section('title', 'Surveys')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Surveys</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Surveys</h2>
        <p class="page-subheading">Create and manage course evaluation surveys.</p>
    </div>
    <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Survey
    </a>
</div>

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.surveys.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                                {{ $sem->full_label }}
                                @if ($sem->is_active) · Active @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" class="form-control auth-input"
                               placeholder="Search title…" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Non-archived</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all"     @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE ===== --}}
<div class="card">
    @if ($surveys->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-ui-checks-grid"></i></div>
            <p class="empty-state-text">No surveys found.</p>
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Survey
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Survey</th>
                        <th>Offering</th>
                        <th>Target</th>
                        <th class="text-center">Questions</th>
                        <th class="text-center">Responses</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($surveys as $survey)
                    <tr class="{{ $survey->trashed() ? 'row-muted' : '' }}">

                        <td>
                            <div class="fw-500">{{ $survey->title }}</div>
                            <div class="text-muted-sm">{{ $survey->offering->semester->full_label }}</div>
                        </td>

                        <td>
                            <div class="text-mono" style="font-size:.8rem;">
                                {{ $survey->offering->subject->course_code }}
                            </div>
                            <div class="text-muted-sm">{{ $survey->offering->teacher->name }}</div>
                        </td>

                        <td>
                            <span class="role-pill role-pill--{{ $survey->targetRole->name }}">
                                {{ ucfirst($survey->targetRole->name) }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="count-badge">
                                {{ $survey->questions_count ?? $survey->questions->count() }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="count-badge count-badge--responses">
                                {{ $survey->attempts()->whereNotNull('submitted_at')->count() }}
                            </span>
                        </td>

                        <td>
                            @if ($survey->trashed())
                                <span class="status-pill status-pill--archived">
                                    <i class="bi bi-archive me-1"></i>Archived
                                </span>
                            @elseif ($survey->is_active)
                                <span class="status-pill status-pill--active">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @else
                                <span class="status-pill status-pill--inactive">
                                    <i class="bi bi-pause-circle me-1"></i>Inactive
                                </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="table-actions">

                                <a href="{{ route('admin.surveys.show', $survey->id) }}"
                                   class="btn btn-sm btn-icon" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if (! $survey->trashed())

                                    <a href="{{ route('admin.surveys.edit', $survey->id) }}"
                                       class="btn btn-sm btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.surveys.toggle-active', $survey->id) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm btn-icon {{ $survey->is_active ? 'btn-icon--warning' : 'btn-icon--success' }}"
                                                title="{{ $survey->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $survey->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>

                                    <form method="POST"
                                          action="{{ route('admin.surveys.destroy', $survey->id) }}"
                                          class="d-inline"
                                          data-confirm="Archive this survey? It will no longer be accessible to respondents.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>

                                @else

                                    <form method="POST"
                                          action="{{ route('admin.surveys.restore', $survey->id) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--success" title="Restore">
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

        @if ($surveys->hasPages())
            <div class="table-pagination">
                {{ $surveys->links() }}
            </div>
        @endif

    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
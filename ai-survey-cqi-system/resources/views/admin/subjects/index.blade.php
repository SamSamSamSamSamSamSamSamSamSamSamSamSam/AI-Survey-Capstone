@extends('layouts.app')
@section('title', 'Subjects')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Subjects</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Subjects</h2>
        <p class="page-subheading">Manage course subjects offered across all programs.</p>
    </div>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Subject
    </a>
</div>

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.subjects.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search"
                               class="form-control auth-input"
                               placeholder="Search code or name…"
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Active</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all"     @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE ===== --}}
<div class="card">
    @if ($subjects->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-book"></i></div>
            <p class="empty-state-text">No subjects found.</p>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Subject
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Name</th>
                        <th class="text-center">Units</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $subject)
                    <tr class="{{ $subject->trashed() ? 'row-muted' : '' }}">

                        <td>
                            <span class="program-code-badge program-code-badge--subject">
                                {{ $subject->course_code }}
                            </span>
                        </td>

                        <td class="fw-500" style="font-size:.875rem;">{{ $subject->name }}</td>

                        <td class="text-center">
                            <span class="count-badge">{{ $subject->units }}</span>
                        </td>

                        <td>
                            @if ($subject->trashed())
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
                                @if (! $subject->trashed())
                                    <a href="{{ route('admin.subjects.edit', $subject->id) }}"
                                       class="btn btn-sm btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.subjects.destroy', $subject->id) }}"
                                          class="d-inline"
                                          data-confirm="Archive the subject &quot;{{ $subject->name }}&quot;?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST"
                                          action="{{ route('admin.subjects.restore', $subject->id) }}"
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

        @if ($subjects->hasPages())
            <div class="table-pagination">{{ $subjects->links() }}</div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
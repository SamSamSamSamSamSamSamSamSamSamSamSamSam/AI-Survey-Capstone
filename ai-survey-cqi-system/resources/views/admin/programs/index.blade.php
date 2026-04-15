@extends('layouts.app')
@section('title', 'Programs')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Programs</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Programs</h2>
        <p class="page-subheading">Manage academic programs offered by the institution.</p>
    </div>
    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Program
    </a>
</div>

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.programs.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
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

                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE ===== --}}
<div class="card">
    @if ($programs->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-mortarboard"></i></div>
            <p class="empty-state-text">No programs found.</p>
            <a href="{{ route('admin.programs.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Program
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Code</th>
                        {{-- <th>Status</th> --}}
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($programs as $program)
                    <tr class="{{ $program->trashed() ? 'row-muted' : '' }}">

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="program-icon">
                                    <i class="bi bi-mortarboard"></i>
                                </div>
                                <span class="fw-500">{{ $program->name }}</span>
                            </div>
                        </td>

                        <td>
                            <span class="program-code-badge">{{ $program->program_code }}</span>
                        </td>

                        {{-- <td>
                            @if ($program->trashed())
                                <span class="status-pill status-pill--archived">
                                    <i class="bi bi-archive me-1"></i>Archived
                                </span>
                            @else
                                <span class="status-pill status-pill--active">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @endif
                        </td> --}}

                        <td class="text-end">
                            <div class="table-actions">

                                <a href="{{ route('admin.programs.show', $program->id) }}"
                                   class="btn btn-sm btn-icon" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if (! $program->trashed())
                                    <a href="{{ route('admin.programs.edit', $program->id) }}"
                                       class="btn btn-sm btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.programs.destroy', $program->id) }}"
                                          class="d-inline"
                                          data-confirm="Archive the program &quot;{{ $program->name }}&quot;?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger"
                                                title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST"
                                          action="{{ route('admin.programs.restore', $program->id) }}"
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

        @if ($programs->hasPages())
            <div class="table-pagination">{{ $programs->links() }}</div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
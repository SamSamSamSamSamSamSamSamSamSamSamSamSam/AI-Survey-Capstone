@extends('layouts.app')
@section('title', 'Curricula')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Curricula</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Curricula</h2>
        <p class="page-subheading">Manage curriculum versions across all academic programs.</p>
    </div>
    <a href="{{ route('admin.curricula.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Curriculum
    </a>
</div>

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.curricula.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select">
                        <option value="">All Programs</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                                {{ $program->program_code }} — {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search"
                               class="form-control auth-input"
                               placeholder="Search code or description…"
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        <option value="deleted"  @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all"      @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.curricula.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE ===== --}}
<div class="card">
    @if ($curricula->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-text"></i></div>
            <p class="empty-state-text">No curricula found.</p>
            <a href="{{ route('admin.curricula.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Curriculum
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Program</th>
                        <th>Description</th>
                        <th class="text-center">Effective Year</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curricula as $curriculum)
                    <tr class="{{ $curriculum->trashed() ? 'row-muted' : '' }}">

                        <td>
                            <span class="program-code-badge">{{ $curriculum->curriculum_code }}</span>
                        </td>

                        <td style="font-size:.82rem;">
                            {{ $curriculum->program->program_code }}
                        </td>

                        <td style="font-size:.82rem;color:#6b7280;max-width:260px;" class="text-truncate">
                            {{ $curriculum->description ?? '—' }}
                        </td>

                        <td class="text-center">
                            <span class="count-badge">{{ $curriculum->effective_year }}</span>
                        </td>

                        <td>
                            @if ($curriculum->trashed())
                                <span class="status-pill status-pill--archived">
                                    <i class="bi bi-archive me-1"></i>Archived
                                </span>
                            @elseif ($curriculum->is_active)
                                <span class="status-pill status-pill--active">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @else
                                <span class="status-pill" style="background:#f3f4f6;color:#6b7280;">
                                    <i class="bi bi-dash-circle me-1"></i>Inactive
                                </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="table-actions">
                                <a href="{{ route('admin.curricula.show', $curriculum->id) }}"
                                   class="btn btn-sm btn-icon" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if (! $curriculum->trashed())
                                    <a href="{{ route('admin.curricula.edit', $curriculum->id) }}"
                                       class="btn btn-sm btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.curricula.toggle-active', $curriculum->id) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm btn-icon {{ $curriculum->is_active ? '' : 'btn-icon--success' }}"
                                                title="{{ $curriculum->is_active ? 'Deactivate' : 'Activate' }}"
                                                style="{{ $curriculum->is_active ? 'color:#d97706;' : '' }}">
                                            <i class="bi bi-toggle-{{ $curriculum->is_active ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>

                                    <form method="POST"
                                          action="{{ route('admin.curricula.destroy', $curriculum->id) }}"
                                          class="d-inline"
                                          data-confirm="Archive the curriculum &quot;{{ $curriculum->curriculum_code }}&quot;?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST"
                                          action="{{ route('admin.curricula.restore', $curriculum->id) }}"
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

        @if ($curricula->hasPages())
            <div class="table-pagination">{{ $curricula->links() }}</div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
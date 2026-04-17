@extends('layouts.app')
@section('title', 'Semesters')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Semesters</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Semesters</h2>
        <p class="page-subheading">Manage academic semesters and control which one is currently active.</p>
    </div>
    <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Semester
    </a>
</div>

@php $activeSemester = \App\Models\Semester::current(); @endphp

@if ($activeSemester)
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bi bi-calendar-check-fill flex-shrink-0"></i>
        <div>Active semester: <strong>{{ $activeSemester->full_label }}</strong></div>
    </div>
@else
    <div class="alert d-flex align-items-center gap-2 mb-3" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
        <div>No active semester is currently set. Course offerings and student enrollment are unavailable until a semester is activated.</div>
    </div>
@endif

<div class="card">
    @if ($semesters->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-calendar3"></i></div>
            <p class="empty-state-text">No semesters found.</p>
            <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Semester
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Semester</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($semesters as $semester)
                    <tr>
                        <td class="fw-500">{{ $semester->full_label }}</td>
                        <td>
                            <span class="program-code-badge">
                                @if ($semester->semester_number == 1) 1st
                                @elseif ($semester->semester_number == 2) 2nd
                                @else Summer
                                @endif
                            </span>
                        </td>
                        <td style="font-size:.875rem;">
                            {{ $semester->academic_start_year }}–{{ $semester->academic_start_year + 1 }}
                        </td>
                        <td>
                            @if ($semester->is_active)
                                <span class="status-pill status-pill--active">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @else
                                <span class="status-pill status-pill--archived">
                                    <i class="bi bi-circle me-1"></i>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="{{ $semester->is_active ? '#' : route('admin.semesters.edit', $semester->id) }}" 
                                    class="btn btn-sm btn-icon {{ $semester->is_active ? 'disabled' : '' }}" 
                                    title="{{ $semester->is_active ? 'Cannot edit while active' : 'Edit' }}"
                                    style="{{ $semester->is_active ? 'pointer-events: auto; cursor: not-allowed;' : '' }}">
                                        
                                    <i class="bi {{ $semester->is_active ? 'bi-lock-fill text-muted' : 'bi-pencil' }}"></i>
                                </a>

                                @if (! $semester->is_active)
                                    <form method="POST"
                                          action="{{ route('admin.semesters.activate', $semester->id) }}"
                                          class="d-inline"
                                          data-confirm="Set &quot;{{ $semester->full_label }}&quot; as the active semester?">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--success" title="Activate">
                                            <i class="bi bi-toggle-off"></i>
                                        </button>
                                    </form>
                                    <form method="POST"
                                          action="{{ route('admin.semesters.destroy', $semester->id) }}"
                                          class="d-inline"
                                          data-confirm="Delete the semester &quot;{{ $semester->full_label }}&quot;? This cannot be undone.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST"
                                          action="{{ route('admin.semesters.deactivate', $semester->id) }}"
                                          class="d-inline"
                                          data-confirm="Deactivate &quot;{{ $semester->full_label }}&quot;? No active semester will be set.">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-icon" title="Deactivate" style="color:#d97706;">
                                            <i class="bi bi-toggle-on"></i>
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

        @if ($semesters->hasPages())
            <div class="table-pagination">{{ $semesters->links() }}</div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
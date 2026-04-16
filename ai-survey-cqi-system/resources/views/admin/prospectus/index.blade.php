@extends('layouts.app')
@section('title', 'Prospectus')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Prospectus</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Prospectus</h2>
        <p class="page-subheading">Browse and manage subject assignments per program curriculum.</p>
    </div>
    <a href="{{ route('admin.prospectus.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Entry
    </a>
</div>

{{-- ===== FILTER: Program → Curriculum ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.prospectus.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select" id="filter-program" onchange="this.form.submit()">
                        <option value="">Select a program…</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                                {{ $program->program_code }} — {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($selectedProgram && $curricula->isNotEmpty())
                    <div class="col-md-4">
                        <label class="form-label">Curriculum</label>
                        <select name="curriculum_id" class="form-select">
                            <option value="">Select a curriculum…</option>
                            @foreach ($curricula as $c)
                                <option value="{{ $c->id }}" @selected(request('curriculum_id') == $c->id)>
                                    {{ $c->display_label }} {{ $c->is_active ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-eye me-1"></i> View
                        </button>
                        <a href="{{ route('admin.prospectus.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    </div>
                @endif

            </div>
        </form>
    </div>
</div>

{{-- ===== STATES ===== --}}
@if (! $selectedProgram)
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-bookmark"></i></div>
            <p class="empty-state-text">Select a program above to view its prospectus.</p>
        </div>
    </div>

@elseif ($selectedProgram && $curricula->isEmpty())
    <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-info-circle-fill flex-shrink-0"></i>
        <div>
            No curricula found for <strong>{{ $selectedProgram->name }}</strong>.
            <a href="{{ route('admin.curricula.create') }}" class="fw-600 ms-1">Create one first →</a>
        </div>
    </div>

@elseif ($selectedCurriculum)

    {{-- Context bar --}}
    <div class="attempts-meta-strip mb-3">
        <div class="attempts-meta-strip__item">
            <i class="bi bi-journal-text me-1"></i>
            <strong>{{ $selectedCurriculum->curriculum_code }}</strong>
        </div>
        <div class="attempts-meta-strip__sep"></div>
        <div class="attempts-meta-strip__item">{{ $selectedProgram->name }}</div>
        <div class="attempts-meta-strip__sep"></div>
        <div class="attempts-meta-strip__item">
            <i class="bi bi-calendar3 me-1"></i>Effective {{ $selectedCurriculum->effective_year }}
        </div>
    </div>

    @if ($grouped->isEmpty())
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
                <p class="empty-state-text">No subjects added to this curriculum yet.</p>
                <a href="{{ route('admin.prospectus.create', ['program_id' => $selectedProgram->id, 'curriculum_id' => $selectedCurriculum->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add First Subject
                </a>
            </div>
        </div>
    @else
        @foreach ($grouped as $label => $entries)

            <div class="program-year-heading">
                <div class="program-year-heading__dot"></div>
                <h3 class="program-year-heading__label">{{ $label }}</h3>
            </div>

            <div class="card mb-3">
                <div class="table-responsive">
                    <table class="table data-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject</th>
                                <th class="text-center">Units</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entries as $entry)
                            <tr>
                                <td>
                                    <span class="program-code-badge program-code-badge--subject">
                                        {{ $entry->subject->course_code }}
                                    </span>
                                </td>
                                <td class="fw-500" style="font-size:.875rem;">{{ $entry->subject->name }}</td>
                                <td class="text-center">
                                    <span class="count-badge">{{ $entry->subject->units }}</span>
                                </td>
                                <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('admin.prospectus.destroy', $entry->id) }}"
                                          class="d-inline"
                                          data-confirm="Remove &quot;{{ $entry->subject->name }}&quot; from this curriculum?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f9fafb;">
                                <td colspan="2" style="font-size:.8rem;color:#6b7280;padding:.5rem 1rem;">
                                    {{ $entries->count() }} {{ Str::plural('subject', $entries->count()) }}
                                </td>
                                <td class="text-center" style="font-size:.8rem;color:#6b7280;">
                                    <strong>{{ $entries->sum('subject.units') }}</strong> units
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        @endforeach
    @endif

@else
    {{-- Program selected but no curriculum chosen yet --}}
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-bookmark"></i></div>
            <p class="empty-state-text">Select a curriculum above to view its prospectus.</p>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
@extends('layouts.app')
@section('title', 'Semesters')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Semesters</li>
</ol>
@endsection

@push('styles')
<style>
/* ── Active Semester Hero Card ─────────────────────────────────────────────── */
.active-semester-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #0d6efd0f 0%, #0d6efd18 100%);
    border: 1px solid #0d6efd30;
    border-left: 4px solid #0d6efd;
    border-radius: .5rem;
    flex-wrap: wrap;
}
.active-semester-card__body { display: flex; flex-direction: column; gap: .25rem; }
.active-semester-card__meta { display: flex; align-items: center; gap: .5rem; }
.active-semester-card__badge {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #0d6efd;
    background: #0d6efd18;
    padding: .2rem .6rem;
    border-radius: 99px;
}
.active-semester-card__ay { font-size: .8rem; color: #6c757d; }
.active-semester-card__name { font-size: 1.35rem; font-weight: 700; color: #1a1a2e; }

/* ── Accordion ─────────────────────────────────────────────────────────────── */
.semester-accordion { display: flex; flex-direction: column; gap: .5rem; }
.semester-accordion .accordion-item { border-radius: .5rem !important; overflow: hidden; border: 1px solid #dee2e6; }
.semester-accordion__item--active { border-color: #0d6efd40 !important; }

.semester-accordion__toggle {
    font-weight: 600;
    font-size: .95rem;
    padding: .9rem 1.25rem;
    background: #fff;
}
.semester-accordion__toggle:not(.collapsed) { background: #f8faff; color: #0d6efd; }
.semester-accordion__toggle:focus { box-shadow: none; }

.semester-accordion__year-label { font-weight: 600; }
.semester-accordion__year-meta { line-height: 1; }

/* ── Semester List ─────────────────────────────────────────────────────────── */
.semester-list { border-top: 1px solid #f0f0f0; }
.semester-list__item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .85rem 1.25rem;
    border-bottom: 1px solid #f5f5f5;
    transition: background .15s;
    position: relative;
}
.semester-list__item:last-child { border-bottom: none; }
.semester-list__item:hover { background: #fafafa; }

.semester-list__item--active { background: #f8faff; }
.semester-list__item--active:hover { background: #f0f5ff; }

/* Left colored stripe for active */
.semester-list__stripe {
    width: 3px;
    height: 1.75rem;
    border-radius: 2px;
    background: transparent;
    flex-shrink: 0;
}
.semester-list__item--active .semester-list__stripe { background: #0d6efd; }

.semester-list__info { flex: 1; display: flex; flex-direction: column; gap: .15rem; }
.semester-list__name { font-weight: 500; font-size: .9rem; display: flex; align-items: center; flex-wrap: wrap; gap: .25rem; }
.semester-list__sub { font-size: .78rem; }
.semester-list__actions { flex-shrink: 0; }

/* ── Change Active Modal Radio Group ──────────────────────────────────────── */
.semester-radio-group { display: flex; flex-direction: column; gap: .25rem; max-height: 360px; overflow-y: auto; }
.semester-radio-group__year {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6c757d;
    padding: .5rem .25rem .25rem;
    margin-top: .25rem;
}
.semester-radio-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem .75rem;
    border: 1px solid #e9ecef;
    border-radius: .375rem;
    cursor: pointer;
    font-size: .875rem;
    transition: background .12s, border-color .12s;
}
.semester-radio-item:hover { background: #f8f9fa; border-color: #adb5bd; }
.semester-radio-item--active { background: #f0f5ff; border-color: #0d6efd40; }
.semester-radio-item input[type="radio"] { flex-shrink: 0; }
</style>
@endpush

@section('content')

{{-- ── Page Header ──────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h2 class="page-heading">Semesters</h2>
        <p class="page-subheading">Manage academic semesters and set which one is currently active.</p>
    </div>
    <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create School Year
    </a>
</div>

{{-- ── Active Semester Hero Card ────────────────────────────────────────────── --}}
@if ($activeSemester)
    <div class="active-semester-card mb-4">
        <div class="active-semester-card__body">
            <div class="active-semester-card__meta">
                <span class="active-semester-card__badge">
                    <i class="bi bi-broadcast me-1"></i> Active
                </span>
                <span class="active-semester-card__ay">{{ $activeSemester->academic_year_label }}</span>
            </div>
            <div class="active-semester-card__name">{{ $activeSemester->semester_label }}</div>
        </div>
        <div class="active-semester-card__actions">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#changeActiveModal">
                <i class="bi bi-arrow-left-right me-1"></i> Change Active Semester
            </button>
        </div>
    </div>
@else
    <div class="alert d-flex align-items-center gap-2 mb-4"
         style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 fs-5"></i>
        <div>
            <strong>No active semester.</strong>
            Course offerings and student enrollment are unavailable until a semester is activated.
        </div>
    </div>
@endif

{{-- ── Search ──────────────────────────────────────────────────────────────── --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('admin.semesters.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <div class="d-flex gap-2">
                        <div class="input-icon-wrap flex-grow-1">
                            <i class="bi bi-search input-icon"></i>
                            <input type="text" 
                                   name="search" 
                                   id="searchInput"
                                   class="form-control auth-input"
                                   placeholder="Search by year…"
                                   value="{{ $search ?? request('search') }}" 
                                   autocomplete="off">
                        </div>
                        <a href="{{ route('admin.semesters.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Semester Groups (Accordion) ─────────────────────────────────────────── --}}
@if ($grouped->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-calendar3"></i></div>
            <p class="empty-state-text">No semesters found.</p>
            <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First School Year
            </a>
        </div>
    </div>
@else
    <div class="accordion semester-accordion" id="semesterAccordion">
        @foreach ($grouped as $year => $semesters)
            @php
                $endYear    = $year + 1;
                $ayLabel    = "S.Y. {$year}–{$endYear}";
                $groupId    = "ay-{$year}";
                $hasActive  = $semesters->contains('is_active', true);
                // Auto-expand: current year group or group with active semester
                $isOpen     = $hasActive || ($loop->first && !$activeSemester);
            @endphp

            <div class="accordion-item semester-accordion__item {{ $hasActive ? 'semester-accordion__item--active' : '' }}">

                {{-- Accordion Header --}}
                <h2 class="accordion-header" id="heading-{{ $groupId }}">
                    <button class="accordion-button {{ $isOpen ? '' : 'collapsed' }} semester-accordion__toggle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $groupId }}"
                            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                            aria-controls="collapse-{{ $groupId }}">

                        <span class="semester-accordion__year-label">{{ $ayLabel }}</span>

                        <span class="semester-accordion__year-meta ms-auto me-3 d-flex align-items-center gap-2">
                            @if ($hasActive)
                                <span class="status-pill status-pill--active">
                                    <i class="bi bi-broadcast me-1"></i>Active
                                </span>
                            @endif
                            <span class="text-muted small">
                                {{ $semesters->count() }} {{ Str::plural('semester', $semesters->count()) }}
                            </span>
                        </span>

                    </button>
                </h2>

                {{-- Accordion Body --}}
                <div id="collapse-{{ $groupId }}"
                     class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}"
                     aria-labelledby="heading-{{ $groupId }}"
                     data-bs-parent="#semesterAccordion">

                    <div class="accordion-body p-0">
                        <ul class="semester-list list-unstyled mb-0">
                            @foreach ($semesters as $semester)
                                <li class="semester-list__item {{ $semester->is_active ? 'semester-list__item--active' : '' }}">

                                    {{-- Active indicator stripe --}}
                                    <div class="semester-list__stripe"></div>

                                    {{-- Semester Info --}}
                                    <div class="semester-list__info">
                                        <span class="semester-list__name">
                                            {{ $semester->semester_label }}
                                            @if ($semester->is_active)
                                                <span class="status-pill status-pill--active ms-2">
                                                    <i class="bi bi-check-circle me-1"></i>Active
                                                </span>
                                            @endif
                                        </span>
                                        <span class="semester-list__sub text-muted small">
                                            {{ $semester->name }}
                                        </span>
                                    </div>

                                    {{-- Actions Dropdown --}}
                                    <div class="semester-list__actions">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    title="Options">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                                {{-- Edit Name --}}
                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('admin.semesters.edit', $semester->id) }}">
                                                        <i class="bi bi-pencil me-2 text-muted"></i> Edit Name
                                                    </a>
                                                </li>

                                                @if (! $semester->is_active)
                                                    {{-- Set Active --}}
                                                    <li>
                                                        <form method="POST"
                                                              action="{{ route('admin.semesters.activate', $semester->id) }}"
                                                              data-confirm="Set &quot;{{ $semester->full_label }}&quot; as the active semester?">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-broadcast me-2 text-success"></i> Set as Active
                                                            </button>
                                                        </form>
                                                    </li>
                                                    {{-- <li><hr class="dropdown-divider"></li> --}}
                                                    {{-- Delete --}}
                                                    {{-- <li>
                                                        <form method="POST"
                                                              action="{{ route('admin.semesters.destroy', $semester->id) }}"
                                                              data-confirm="Delete &quot;{{ $semester->full_label }}&quot;? This cannot be undone.">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li> --}}
                                                @else
                                                    <li><hr class="dropdown-divider"></li>
                                                    {{-- Deactivate --}}
                                                    <li>
                                                        <form method="POST"
                                                              action="{{ route('admin.semesters.deactivate', $semester->id) }}"
                                                              data-confirm="Deactivate &quot;{{ $semester->full_label }}&quot;? No active semester will be set.">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="dropdown-item text-warning">
                                                                <i class="bi bi-stop-circle me-2"></i> Deactivate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                            </ul>
                                        </div>
                                    </div>

                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- ── Change Active Semester Modal ────────────────────────────────────────── --}}
<div class="modal fade" id="changeActiveModal" tabindex="-1" aria-labelledby="changeActiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="changeActiveModalLabel">
                    <i class="bi bi-arrow-left-right me-2"></i>Change Active Semester
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">
                    Select a semester to make active. The current active semester will be deactivated automatically.
                </p>

                @php
                    $allSemesters = \App\Models\Semester::orderByDesc('academic_start_year')
                        ->orderBy('semester_number')
                        ->get()
                        ->groupBy('academic_start_year');
                @endphp

                <form method="POST" action="" id="changeActiveForm">
                    @csrf @method('PATCH')
                    <div class="semester-radio-group">
                        @foreach ($allSemesters as $year => $sems)
                            <div class="semester-radio-group__year">
                                S.Y. {{ $year }}–{{ $year + 1 }}
                            </div>
                            @foreach ($sems as $sem)
                                <label class="semester-radio-item {{ $sem->is_active ? 'semester-radio-item--active' : '' }}"
                                       data-action="{{ route('admin.semesters.activate', $sem->id) }}">
                                    <input type="radio"
                                           name="active_semester"
                                           value="{{ $sem->id }}"
                                           {{ $sem->is_active ? 'checked' : '' }}
                                           class="form-check-input me-2">
                                    <span>{{ $sem->semester_label }}</span>
                                    @if ($sem->is_active)
                                        <span class="badge bg-success ms-auto" style="font-size:.7rem;">Current</span>
                                    @endif
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="confirmChangeActive">
                    <i class="bi bi-check-lg me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
<script>
// Change Active Semester modal — wire radio selection to form action
document.addEventListener('DOMContentLoaded', function () {
    const confirmBtn = document.getElementById('confirmChangeActive');
    if (!confirmBtn) return;

    confirmBtn.addEventListener('click', function () {
        const selected = document.querySelector('.semester-radio-item input[type="radio"]:checked');
        if (!selected) return;

        const label = selected.closest('.semester-radio-item');
        const action = label ? label.dataset.action : null;
        if (!action) return;

        const form = document.getElementById('changeActiveForm');
        form.action = action;
        form.submit();
    });

    // Highlight selected radio item
    document.querySelectorAll('.semester-radio-item input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.semester-radio-item').forEach(el => el.classList.remove('semester-radio-item--selected'));
            if (this.checked) {
                this.closest('.semester-radio-item').classList.add('semester-radio-item--selected');
            }
        });
    });
});
</script>
@endpush
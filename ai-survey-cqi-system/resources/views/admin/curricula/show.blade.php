@extends('layouts.app')
@section('title', $curriculum->curriculum_code)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.curricula.index') }}">Curricula</a></li>
    <li class="breadcrumb-item active">{{ $curriculum->curriculum_code }}</li>
</ol>
@endsection

@section('content')

@php
    $totalSubjects = $grouped->flatten(1)->count();
    $totalUnits    = $grouped->flatten(1)->sum('subject.units');
@endphp

{{-- ===== PAGE HEADER ===== --}}
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="program-icon program-icon--lg">
            <i class="bi bi-journal-text"></i>
        </div>
        <div>
            <h2 class="page-heading d-flex align-items-center gap-2 flex-wrap">
                {{ $curriculum->curriculum_code }}
                @if ($curriculum->is_active)
                    <span class="status-pill status-pill--active">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </span>
                @else
                    <span class="status-pill" style="background:#f3f4f6;color:#6b7280;">
                        <i class="bi bi-dash-circle me-1"></i>Inactive
                    </span>
                @endif
            </h2>
            <p class="page-subheading">
                {{ $curriculum->program->program_code }} — {{ $curriculum->program->name }}
                @if ($curriculum->description)
                    &nbsp;·&nbsp; {{ $curriculum->description }}
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.curricula.toggle-active', $curriculum->id) }}" class="d-inline">
            @csrf @method('PATCH')
            <button type="submit"
                    class="btn btn-sm {{ $curriculum->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                <i class="bi bi-toggle-{{ $curriculum->is_active ? 'on' : 'off' }} me-1"></i>
                {{ $curriculum->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('admin.curricula.edit', $curriculum->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.curricula.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== STATS STRIP ===== --}}
<div class="attempts-meta-strip mb-4">
    <div class="attempts-meta-strip__item">
        <i class="bi bi-calendar3 me-1"></i>
        Effective <strong>{{ $curriculum->effective_year }}</strong>
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-book me-1"></i>
        <strong>{{ $totalSubjects }}</strong> {{ Str::plural('subject', $totalSubjects) }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-stack me-1"></i>
        <strong>{{ $totalUnits }}</strong> total units
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-clock me-1"></i>
        Created {{ $curriculum->created_at->format('M d, Y') }}
    </div>
</div>

{{-- ===== PROSPECTUS ===== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">Prospectus</p>
    <a href="{{ route('admin.prospectus.create', ['program_id' => $curriculum->program_id, 'curriculum_id' => $curriculum->id]) }}"
       class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-plus-lg me-1"></i> Add Subject
    </a>
</div>

@if ($grouped->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
            <p class="empty-state-text">No subjects in this curriculum yet.</p>
            <a href="{{ route('admin.prospectus.create', ['program_id' => $curriculum->program_id, 'curriculum_id' => $curriculum->id]) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Subjects
            </a>
        </div>
    </div>
@else
    @foreach ($grouped as $label => $entries)

        {{-- Year/Semester heading --}}
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
                            <td class="fw-500" style="font-size:.875rem;">
                                {{ $entry->subject->name }}
                            </td>
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

@endsection
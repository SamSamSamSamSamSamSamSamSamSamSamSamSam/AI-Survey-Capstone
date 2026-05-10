@extends('layouts.app')
@section('title', $program->program_code)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programs</a></li>
    <li class="breadcrumb-item active">{{ $program->program_code }}</li>
</ol>
@endsection

@section('content')

@php
    $nestedGrouped = $program->curricula
        ->flatMap(fn($c) => $c->prospectuses)
        ->groupBy('year_level_label')
        ->map(fn($yearGroup) => $yearGroup->groupBy('semester_label'));

    $totalSubjects = $nestedGrouped->flatten(2)->count();
@endphp

{{-- ===== PAGE HEADER ===== --}}
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="program-icon program-icon--lg">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <h2 class="page-heading d-flex align-items-center gap-2 flex-wrap">
                {{ $program->name }}
                <span class="program-code-badge">{{ $program->program_code }}</span>
                @if ($program->trashed())
                    <span class="status-pill status-pill--archived">
                        <i class="bi bi-archive me-1"></i>Archived
                    </span>
                @else
                    <span class="status-pill status-pill--active">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </span>
                @endif
            </h2>
            <p class="page-subheading">Academic program details and curriculum overview.</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if (! $program->trashed())
            <a href="{{ route('admin.programs.edit', $program->id) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endif
        <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== STATS STRIP ===== --}}
<div class="attempts-meta-strip mb-4">
    <div class="attempts-meta-strip__item">
        <i class="bi bi-journal-text me-1"></i>
        <strong>{{ $program->curricula->count() }}</strong> curricul{{ $program->curricula->count() === 1 ? 'um' : 'a' }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-book me-1"></i>
        <strong>{{ $totalSubjects }}</strong> prospectus {{ Str::plural('entry', $totalSubjects) }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-calendar3 me-1"></i>
        Created {{ $program->created_at->format('M d, Y') }}
    </div>
</div>

{{-- ===== PROSPECTUS ===== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="card-section-label mb-0">
        Curriculum Overview
    </p>
    <a href="{{ route('admin.prospectus.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Subject
    </a>
</div>

@if ($nestedGrouped->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
            <p class="empty-state-text">No prospectus entries yet.</p>
            <a href="{{ route('admin.prospectus.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Subjects
            </a>
        </div>
    </div>
@else
    @foreach ($nestedGrouped as $yearLabel => $semesters)

        {{-- ── Year Level heading ── --}}
        <div class="program-year-heading">
            <div class="program-year-heading__dot"></div>
            <h3 class="program-year-heading__label">{{ $yearLabel }}</h3>
        </div>

        @foreach ($semesters as $semesterLabel => $entries)
        <div class="card mb-3">

            {{-- Semester sub-header --}}
            <div class="program-semester-header">
                <i class="bi bi-calendar3 me-2 text-muted"></i>
                {{ $semesterLabel }}
                <span class="ms-auto count-badge">{{ $entries->count() }} subject(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject</th>
                            <th class="text-center">Units</th>
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
                            <td class="fw-500" style="font-size: .875rem;">
                                {{ $entry->subject->name }}
                            </td>
                            <td class="text-center">
                                <span class="count-badge">{{ $entry->subject->units }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        @endforeach

    @endforeach
@endif

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
@extends('admin.layouts.app')
@section('title', $program->program_code)

@section('content')
<div class="page-header">
    <h1>{{ $program->name }} <small style="font-size:.85rem;color:#6b7280;font-weight:400;">{{ $program->program_code }}</small></h1>
    <div class="actions">
        <a href="{{ route('admin.programs.edit', $program->id) }}" class="btn btn-secondary">Edit</a>
        <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

{{-- Prospectus preview grouped by year + semester --}}
@php
    $grouped = $program->prospectuses
        ->groupBy(fn($p) => $p->year_level_label . ' — ' . $p->semester_label);
@endphp

@if ($grouped->isEmpty())
    <div class="card">
        <p class="empty-state">
            No prospectus entries yet.
            <a href="{{ route('admin.prospectus.create') }}">Add subjects to this program.</a>
        </p>
    </div>
@else
    @foreach ($grouped as $label => $entries)
    <div class="card" style="margin-bottom:1rem;">
        <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">
            {{ $label }}
        </div>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject</th>
                    <th>Units</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                <tr>
                    <td>{{ $entry->subject->course_code }}</td>
                    <td>{{ $entry->subject->name }}</td>
                    <td>{{ $entry->subject->units }}</td>
                    <td>{{ $entry->offeringType?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
@endif
@endsection

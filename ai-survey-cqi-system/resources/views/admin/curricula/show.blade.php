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
<div class="page-header">
    <h1>
        {{ $curriculum->curriculum_code }}
        @if ($curriculum->is_active)
            <span class="badge badge-active" style="font-size:.75rem;vertical-align:middle;">Active</span>
        @else
            <span class="badge badge-inactive" style="font-size:.75rem;vertical-align:middle;">Inactive</span>
        @endif
    </h1>
    <div class="actions">
        <a href="{{ route('admin.curricula.edit', $curriculum->id) }}" class="btn btn-secondary">Edit</a>
        <form method="POST" action="{{ route('admin.curricula.toggle-active', $curriculum->id) }}">
            @csrf @method('PATCH')
            <button class="btn {{ $curriculum->is_active ? 'btn-warning' : 'btn-success' }}">
                {{ $curriculum->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

{{-- Details --}}
<div class="card" style="max-width:500px;margin-bottom:1.25rem;">
    <div class="card-body">
        <table style="font-size:.875rem;width:100%;">
            <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;width:140px;">Program</td><td>{{ $curriculum->program->program_code }} — {{ $curriculum->program->name }}</td></tr>
            <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Code</td><td>{{ $curriculum->curriculum_code }}</td></tr>
            <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Description</td><td>{{ $curriculum->description ?? '—' }}</td></tr>
            <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Effective Year</td><td>{{ $curriculum->effective_year }}</td></tr>
        </table>
    </div>
</div>

{{-- Prospectus --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
    <p class="form-text" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;">Prospectus</p>
    <a href="{{ route('admin.prospectus.create', ['program_id' => $curriculum->program_id, 'curriculum_id' => $curriculum->id]) }}" class="btn btn-sm btn-primary">+ Add Subject</a>
</div>

@if ($grouped->isEmpty())
    <div class="card">
        <p class="empty-state">No subjects in this curriculum yet. <a href="{{ route('admin.prospectus.create', ['program_id' => $curriculum->program_id]) }}">Add one.</a></p>
    </div>
@else
    @foreach ($grouped as $label => $entries)
    <div class="card" style="margin-bottom:1rem;">
        <div style="padding:.7rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;display:flex;justify-content:space-between;">
            <span>{{ $label }}</span>
            <span style="font-weight:400;color:#6b7280;font-size:.8rem;">{{ $entries->count() }} subject(s) · {{ $entries->sum('subject.units') }} units</span>
        </div>
        <table>
            <thead>
                <tr><th>Code</th><th>Subject</th><th>Units</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                <tr>
                    <td>{{ $entry->subject->course_code }}</td>
                    <td>{{ $entry->subject->name }}</td>
                    <td>{{ $entry->subject->units }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.prospectus.destroy', $entry->id) }}" onsubmit="return confirm('Remove this subject from the curriculum?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
@endif
@endsection

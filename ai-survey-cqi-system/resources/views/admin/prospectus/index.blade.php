@extends('admin.layouts.app')
@section('title', 'Prospectus')

@section('content')
<div class="page-header">
    <h1>Prospectus</h1>
    <a href="{{ route('admin.prospectus.create') }}" class="btn btn-primary">+ Add Entry</a>
</div>

{{-- Step 1: select program; Step 2: select curriculum --}}
<form method="GET" action="{{ route('admin.prospectus.index') }}">
    <div class="filters">
        <select name="program_id" class="form-control" style="min-width:240px;" onchange="this.form.submit()">
            <option value="">Select a program…</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                    {{ $program->program_code }} — {{ $program->name }}
                </option>
            @endforeach
        </select>

        @if ($selectedProgram && $curricula->isNotEmpty())
            <select name="curriculum_id" class="form-control" style="min-width:220px;">
                <option value="">Select a curriculum…</option>
                @foreach ($curricula as $c)
                    <option value="{{ $c->id }}" @selected(request('curriculum_id') == $c->id)>
                        {{ $c->display_label }} {{ $c->is_active ? '(Active)' : '' }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">View</button>
        @endif
    </div>
</form>

@if ($selectedProgram && $curricula->isEmpty())
    <div class="alert alert-info">
        No curricula found for <strong>{{ $selectedProgram->name }}</strong>.
        <a href="{{ route('admin.curricula.create') }}" style="font-weight:600;">Create one first →</a>
    </div>
@endif

@if ($selectedCurriculum)
    <div style="margin-bottom:1rem;font-size:.875rem;color:#6b7280;">
        Showing: <strong>{{ $selectedCurriculum->curriculum_code }}</strong> — {{ $selectedProgram->name }}
        &nbsp;·&nbsp; Effective {{ $selectedCurriculum->effective_year }}
    </div>

    @if ($grouped->isEmpty())
        <div class="card">
            <p class="empty-state">
                No subjects added to this curriculum yet.
                <a href="{{ route('admin.prospectus.create', ['program_id' => $selectedProgram->id, 'curriculum_id' => $selectedCurriculum->id]) }}">Add one now.</a>
            </p>
        </div>
    @else
        @foreach ($grouped as $label => $entries)
        <div class="card" style="margin-bottom:1rem;">
            <div style="padding:.7rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;display:flex;justify-content:space-between;align-items:center;">
                <span>{{ $label }}</span>
                <span style="font-weight:400;color:#6b7280;font-size:.8rem;">
                    {{ $entries->count() }} subject(s)
                </span>
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
                            <form method="POST" action="{{ route('admin.prospectus.destroy', $entry->id) }}"
                                  onsubmit="return confirm('Remove this entry?')">
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
@elseif (! $selectedProgram)
    <div class="card">
        <p class="empty-state">Select a program above to view its prospectus.</p>
    </div>
@endif
@endsection

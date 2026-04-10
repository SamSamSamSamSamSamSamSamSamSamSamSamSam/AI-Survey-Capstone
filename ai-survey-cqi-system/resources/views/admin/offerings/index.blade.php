@extends('admin.layouts.app')
@section('title', 'Course Offerings')

@section('content')
<div class="page-header">
    <h1>Course Offerings</h1>
    <a href="{{ route('admin.offerings.create') }}" class="btn btn-primary">+ New Offering</a>
</div>

@if (! $activeSemester)
    <div class="alert" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;">
        No active semester is set. You are viewing all offerings.
        <a href="{{ route('admin.semesters.index') }}" style="color:#9a3412;font-weight:600;">Manage Semesters →</a>
    </div>
@endif

<form method="GET" action="{{ route('admin.offerings.index') }}">
    <div class="filters">
        <select name="semester_id" class="form-control" style="min-width:220px;">
            <option value="">All Semesters</option>
            @foreach ($semesters as $sem)
                <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                    {{ $sem->full_label }} {{ $sem->is_active ? '(Active)' : '' }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search subject…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">Active</option>
            <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
            <option value="all"     @selected(request('status') === 'all')>All</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.offerings.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

<div class="card">
    @if ($offerings->isEmpty())
        <p class="empty-state">No course offerings found for this semester.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Faculty</th>
                    <th>Semester</th>
                    <th>Type</th>
                    <th>Group</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($offerings as $offering)
                <tr class="{{ $offering->trashed() ? 'archived' : '' }}">
                    <td>
                        <div>{{ $offering->subject->course_code }}</div>
                        <div style="font-size:.8rem;color:#6b7280;">{{ $offering->subject->name }}</div>
                    </td>
                    <td>{{ $offering->teacher->name }}</td>
                    <td style="font-size:.8rem;">{{ $offering->semester->full_label }}</td>
                    <td>{{ $offering->offeringType?->name ?? '—' }}</td>
                    <td>{{ $offering->group_number ?? '—' }}</td>
                    <td>{{ $offering->enrollments_count ?? $offering->enrollments()->count() }}</td>
                    <td>
                        @if ($offering->trashed())
                            <span class="badge badge-archived">Archived</span>
                        @else
                            <span class="badge badge-active">Active</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.offerings.show', $offering->id) }}" class="btn btn-sm btn-secondary">View</a>
                            @if (! $offering->trashed())
                                <a href="{{ route('admin.offerings.edit', $offering->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-sm btn-warning">Enrollments</a>
                                <form method="POST" action="{{ route('admin.offerings.destroy', $offering->id) }}" onsubmit="return confirm('Archive this offering?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.offerings.restore', $offering->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success">Restore</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $offerings->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

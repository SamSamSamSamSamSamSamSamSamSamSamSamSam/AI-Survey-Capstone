@extends('admin.layouts.app')
@section('title', 'Surveys')

@section('content')
<div class="page-header">
    <h1>Surveys</h1>
    <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">+ New Survey</a>
</div>

<form method="GET" action="{{ route('admin.surveys.index') }}">
    <div class="filters">
        <select name="semester_id" class="form-control" style="min-width:220px;">
            <option value="">All Semesters</option>
            @foreach ($semesters as $sem)
                <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                    {{ $sem->full_label }} {{ $sem->is_active ? '(Active)' : '' }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search title…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">Non-archived</option>
            <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
            <option value="all"     @selected(request('status') === 'all')>All</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

<div class="card">
    @if ($surveys->isEmpty())
        <p class="empty-state">No surveys found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Offering</th>
                    <th>Target Role</th>
                    <th>Questions</th>
                    <th>Responses</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($surveys as $survey)
                <tr class="{{ $survey->trashed() ? 'archived' : '' }}">
                    <td>
                        <div style="font-weight:500;">{{ $survey->title }}</div>
                        <div style="font-size:.78rem;color:#6b7280;">{{ $survey->offering->semester->full_label }}</div>
                    </td>
                    <td style="font-size:.82rem;">
                        {{ $survey->offering->subject->course_code }}<br>
                        <span style="color:#6b7280;">{{ $survey->offering->teacher->name }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $survey->targetRole->name }}">
                            {{ ucfirst($survey->targetRole->name) }}
                        </span>
                    </td>
                    <td>{{ $survey->questions_count ?? $survey->questions->count() }}</td>
                    <td>{{ $survey->attempts()->whereNotNull('submitted_at')->count() }}</td>
                    <td>
                        @if ($survey->trashed())
                            <span class="badge badge-archived">Archived</span>
                        @elseif ($survey->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-sm btn-secondary">View</a>
                            @if (! $survey->trashed())
                                <a href="{{ route('admin.surveys.edit', $survey->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.surveys.toggle-active', $survey->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $survey->is_active ? 'btn-warning' : 'btn-success' }}">
                                        {{ $survey->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.surveys.destroy', $survey->id) }}" onsubmit="return confirm('Archive this survey?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.surveys.restore', $survey->id) }}">
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
        <div class="pagination">{{ $surveys->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

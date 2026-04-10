@extends('admin.layouts.app')
@section('title', 'Subjects')

@section('content')
<div class="page-header">
    <h1>Subjects</h1>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">+ New Subject</a>
</div>

<form method="GET" action="{{ route('admin.subjects.index') }}">
    <div class="filters">
        <input type="text" name="search" class="form-control" placeholder="Search code or name…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">Active</option>
            <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
            <option value="all"     @selected(request('status') === 'all')>All</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

<div class="card">
    @if ($subjects->isEmpty())
        <p class="empty-state">No subjects found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Name</th>
                    <th>Units</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $subject)
                <tr class="{{ $subject->trashed() ? 'archived' : '' }}">
                    <td>{{ $subject->course_code }}</td>
                    <td>{{ $subject->name }}</td>
                    <td>{{ $subject->units }}</td>
                    <td>
                        @if ($subject->trashed())
                            <span class="badge badge-archived">Archived</span>
                        @else
                            <span class="badge badge-active">Active</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                            @if (! $subject->trashed())
                                <form method="POST" action="{{ route('admin.subjects.destroy', $subject->id) }}" onsubmit="return confirm('Archive this subject?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.subjects.restore', $subject->id) }}">
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
        <div class="pagination">{{ $subjects->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

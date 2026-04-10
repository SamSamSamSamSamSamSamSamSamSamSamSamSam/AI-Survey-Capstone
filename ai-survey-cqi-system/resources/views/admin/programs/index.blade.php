@extends('admin.layouts.app')
@section('title', 'Programs')

@section('content')
<div class="page-header">
    <h1>Programs</h1>
    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">+ New Program</a>
</div>

<form method="GET" action="{{ route('admin.programs.index') }}">
    <div class="filters">
        <input type="text" name="search" class="form-control" placeholder="Search code or name…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">Active</option>
            <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
            <option value="all"     @selected(request('status') === 'all')>All</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

<div class="card">
    @if ($programs->isEmpty())
        <p class="empty-state">No programs found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programs as $program)
                <tr class="{{ $program->trashed() ? 'archived' : '' }}">
                    <td>{{ $program->program_code }}</td>
                    <td>{{ $program->name }}</td>
                    <td>
                        @if ($program->trashed())
                            <span class="badge badge-archived">Archived</span>
                        @else
                            <span class="badge badge-active">Active</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.programs.show', $program->id) }}" class="btn btn-sm btn-secondary">View</a>
                            @if (! $program->trashed())
                                <a href="{{ route('admin.programs.edit', $program->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.programs.destroy', $program->id) }}" onsubmit="return confirm('Archive this program?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.programs.restore', $program->id) }}">
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
        <div class="pagination">{{ $programs->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

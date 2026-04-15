@extends('layouts.app')
@section('title', 'Curricula')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Curricula</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Curricula</h1>
    <a href="{{ route('admin.curricula.create') }}" class="btn btn-primary">+ New Curriculum</a>
</div>

<form method="GET" action="{{ route('admin.curricula.index') }}">
    <div class="filters">
        <select name="program_id" class="form-control" style="min-width:220px;">
            <option value="">All Programs</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                    {{ $program->program_code }} — {{ $program->name }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search code or description…" value="{{ request('search') }}">
        <select name="status" class="form-control">
            <option value="">All Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            <option value="deleted"  @selected(request('status') === 'deleted')>Archived</option>
            <option value="all"      @selected(request('status') === 'all')>All</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

<div class="card">
    @if ($curricula->isEmpty())
        <p class="empty-state">No curricula found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Program</th>
                    <th>Description</th>
                    <th>Effective Year</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($curricula as $curriculum)
                <tr class="{{ $curriculum->trashed() ? 'archived' : '' }}">
                    <td><strong>{{ $curriculum->curriculum_code }}</strong></td>
                    <td style="font-size:.82rem;">{{ $curriculum->program->program_code }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $curriculum->description ?? '—' }}</td>
                    <td>{{ $curriculum->effective_year }}</td>
                    <td>
                        @if ($curriculum->trashed())
                            <span class="badge badge-archived">Archived</span>
                        @elseif ($curriculum->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.curricula.show', $curriculum->id) }}" class="btn btn-sm btn-secondary">View</a>

                            @if (! $curriculum->trashed())
                                <a href="{{ route('admin.curricula.edit', $curriculum->id) }}" class="btn btn-sm btn-secondary">Edit</a>

                                <form method="POST" action="{{ route('admin.curricula.toggle-active', $curriculum->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $curriculum->is_active ? 'btn-warning' : 'btn-success' }}">
                                        {{ $curriculum->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.curricula.destroy', $curriculum->id) }}" onsubmit="return confirm('Archive this curriculum?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.curricula.restore', $curriculum->id) }}">
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
        <div class="pagination">{{ $curricula->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

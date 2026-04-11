@extends('layouts.app')
@section('title', 'User Management')

@section('content')

<div class="users-page">


{{-- HEADER --}}
<div class="page-header">
    <div>
        <h1>User Management</h1>
        <p class="page-subtitle">
            Manage system users, roles, and access control
        </p>
    </div>

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New User
    </a>

</div>

{{-- FILTER CARD --}}
<div class="card filter-card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Name, email, ID..."
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Active</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Deactivated</option>
                        <option value="all" @selected(request('status') === 'all')>All</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary">
                        Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="card mt-3">
    @if ($users->isEmpty())
        <div class="empty-state">
            No users found.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Verified</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr class="{{ $user->trashed() ? 'table-muted' : '' }}">
                        <td>{{ $user->user_id_number }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse ($user->roles as $role)
                                <span class="badge role-badge role-{{ $role->name }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @empty
                                <span class="badge bg-secondary">No Role</span>
                            @endforelse
                        </td>
                        <td>
                            {!! $user->email_verified_at
                                ? '<span class="text-success">✔</span>'
                                : '<span class="text-muted">—</span>' !!}
                        </td>
                        <td>
                            @if ($user->trashed())
                                <span class="status-badge status-pending">Deactivated</span>
                            @else
                                <span class="status-badge status-improved">Active</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-light">
                                    View
                                </a>
                                @if (! $user->trashed())
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-light">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-warning">Reset</button>
                                    </form>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Deactivate</button>
                                        </form>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-success">Restore</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
</div>
@endsection

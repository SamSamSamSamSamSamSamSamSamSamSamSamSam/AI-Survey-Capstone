@extends('admin.layouts.app')
@section('title', 'User Management')

@push('styles')
<style>
        /* Page header */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.4rem; }
        .btn { display: inline-block; padding: .5rem 1.1rem; border-radius: 6px; font-size: .875rem; cursor: pointer; text-decoration: none; border: none; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-sm { padding: .3rem .75rem; font-size: .8rem; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #fecaca; }
        .btn-success { background: #d1fae5; color: #065f46; }
        .btn-success:hover { background: #a7f3d0; }
        .btn-warning { background: #fef3c7; color: #92400e; }
        .btn-warning:hover { background: #fde68a; }

        /* Alerts */
        .alert { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: .875rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Filters */
        .filters { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .filters input, .filters select { padding: .45rem .75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .875rem; }
        .filters input:focus, .filters select:focus { outline: none; border-color: #6366f1; }

        /* Table */
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,.07); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead { background: #f9fafb; }
        th { padding: .75rem 1rem; text-align: left; font-weight: 600; color: #6b7280; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb; }
        td { padding: .75rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr.deleted td { opacity: .55; }

        /* Badges */
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
        .badge-admin   { background: #fee2e2; color: #dc2626; }
        .badge-faculty { background: #dbeafe; color: #1d4ed8; }
        .badge-student { background: #d1fae5; color: #065f46; }
        .badge-default { background: #f3f4f6; color: #374151; }
        .badge-deleted { background: #fef3c7; color: #92400e; }

        /* Pagination */
        .pagination { display: flex; gap: .35rem; justify-content: flex-end; padding: 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .35rem .7rem; border-radius: 5px; font-size: .8rem; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; }
        .pagination a:hover { background: #f3f4f6; }
        .pagination .active { background: #4f46e5; color: #fff; border-color: #4f46e5; }

        .actions { display: flex; gap: .4rem; flex-wrap: wrap; }
        .empty { text-align: center; padding: 3rem; color: #9ca3af; font-size: .9rem; }
</style>
@endpush

@section('content')
<div class="layout">
    <div class="main">

        <div class="page-header">
            <h1>User Management</h1>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ New User</a>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="filters">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, email, ID number…"
                    style="min-width: 240px;"
                >

                <select name="role">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>

                <select name="status">
                    <option value="" @selected(!request('status'))>Active Users</option>
                    <option value="deleted" @selected(request('status') === 'deleted')>Deactivated</option>
                    <option value="all" @selected(request('status') === 'all')>All Users</option>
                </select>

                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            @if ($users->isEmpty())
                <p class="empty">No users found.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Verified</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="{{ $user->trashed() ? 'deleted' : '' }}">
                                <td>{{ $user->user_id_number }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge badge-{{ $role->name }}">{{ ucfirst($role->name) }}</span>
                                    @empty
                                        <span class="badge badge-default">No Role</span>
                                    @endforelse
                                </td>
                                <td>{{ $user->email_verified_at ? '✓' : '—' }}</td>
                                <td>
                                    @if ($user->trashed())
                                        <span class="badge badge-deleted">Deactivated</span>
                                    @else
                                        <span class="badge badge-student">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-secondary">View</a>

                                        @if (! $user->trashed())
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-secondary">Edit</a>

                                            {{-- Reset Password --}}
                                            <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}" onsubmit="return confirm('Reset password for {{ $user->name }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">Reset PW</button>
                                            </form>

                                            {{-- Soft Delete --}}
                                            @if ($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Deactivate {{ $user->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Deactivate</button>
                                                </form>
                                            @endif
                                        @else
                                            {{-- Restore --}}
                                            <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                            </form>

                                            {{-- Force Delete --}}
                                            <form method="POST" action="{{ route('admin.users.force-delete', $user->id) }}" onsubmit="return confirm('Permanently delete {{ $user->name }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="pagination">
                    {{ $users->links('pagination::simple-tailwind') }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

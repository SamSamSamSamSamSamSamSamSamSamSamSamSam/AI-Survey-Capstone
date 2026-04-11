@extends('layouts.app')
@section('title', 'User Management')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">User Management</h2>
        <p class="page-subheading">Manage system users, roles, and access control.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> New User
    </a>
</div>

{{-- ===== FILTER CARD ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search"
                               value="{{ request('search') }}"
                               class="form-control auth-input"
                               placeholder="Name, email, ID…">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                @selected(request('role') === $role->name)>
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
                        <option value="all"     @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE CARD ===== --}}
<div class="card">
    @if ($users->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-people"></i></div>
            <p class="empty-state-text">No users found.</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i> Create First User
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID Number</th>
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
                    <tr class="{{ $user->trashed() ? 'row-muted' : '' }}">

                        <td class="text-mono">{{ $user->user_id_number }}</td>

                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="fw-500">{{ $user->name }}</span>
                            </div>
                        </td>

                        <td class="text-muted-sm">{{ $user->email }}</td>

                        <td>
                            @forelse ($user->roles as $role)
                                <span class="role-pill role-pill--{{ $role->name }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @empty
                                <span class="role-pill role-pill--none">No Role</span>
                            @endforelse
                        </td>

                        <td>
                            @if ($user->email_verified_at)
                                <span class="verified-badge verified-badge--yes">
                                    <i class="bi bi-check-circle-fill"></i> Verified
                                </span>
                            @else
                                <span class="verified-badge verified-badge--no">
                                    <i class="bi bi-dash-circle"></i> Unverified
                                </span>
                            @endif
                        </td>

                        <td>
                            @if ($user->trashed())
                                <span class="status-pill status-pill--inactive">Deactivated</span>
                            @else
                                <span class="status-pill status-pill--active">Active</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="table-actions">

                                <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="btn btn-sm btn-icon" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if (! $user->trashed())

                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                       class="btn btn-sm btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.users.reset-password', $user->id) }}"
                                          class="d-inline"
                                          data-confirm="Send a new password to {{ $user->email }}?">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--warning" title="Reset Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </form>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('admin.users.destroy', $user->id) }}"
                                              class="d-inline"
                                              data-confirm="Deactivate {{ $user->name }}?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Deactivate">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    @endif

                                @else

                                    <form method="POST"
                                          action="{{ route('admin.users.restore', $user->id) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--success" title="Restore">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    </form>

                                @endif

                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="table-pagination">
                {{ $users->links() }}
            </div>
        @endif

    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
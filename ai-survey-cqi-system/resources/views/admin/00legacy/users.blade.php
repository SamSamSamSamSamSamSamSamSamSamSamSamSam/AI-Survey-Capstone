@extends('layouts.default')

@section('content')

{{-- Page Header --}}
<div class="dash-header">
    <div class="dash-header__left">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Users</li>
            </ol>
        </nav>
        <h1 class="dash-header__title">Manage Users</h1>
        <p class="dash-header__subtitle">View, search, and manage all registered users.</p>
    </div>
    <div class="dash-header__actions">
        <a href="{{ route('admin.subjects.index') }}" class="cbtn cbtn--success cbtn--sm">
            <i class="bi bi-journal-text me-1"></i> Manage Courses
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="dash-filters mb-4">
    <div class="dash-filters__selects">

        <div class="dash-filter-group">
            <label class="dash-filter-group__label" for="userSearch">
                <i class="bi bi-search me-1"></i> Search
            </label>
            <div class="search-wrap">
                <input type="text"
                       id="userSearch"
                       class="form-control form-control-sm"
                       placeholder="Name or email...">
                <button class="search-clear d-none" id="searchClear" aria-label="Clear search">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="dash-filter-group">
            <label class="dash-filter-group__label" for="roleFilter">
                <i class="bi bi-person-badge me-1"></i> Role
            </label>
            <select id="roleFilter" class="form-select form-select-sm">
                <option value="all">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="dash-filters__links">
        <span class="results-count" id="resultsCount">
            {{ $users->total() }} {{ Str::plural('user', $users->total()) }}
        </span>
    </div>
</div>

{{-- Users Table --}}
<div class="dash-card">
    <div class="dash-card__body p-0">
        <div class="table-responsive">
            <table class="table table-hover dash-table mb-0" id="usersTable">
                <thead>
                    <tr>
                        <th style="width: 56px;">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center" style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="user-row"
                            data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}"
                            data-roles="{{ $user->roles->pluck('name')->toJson() }}">

                            <td class="text-muted">
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="users-name-cell">
                                    <div class="users-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold user-name">{{ $user->name }}</span>
                                </div>
                            </td>

                            <td class="user-email text-muted">{{ $user->email }}</td>

                            <td class="user-roles">
                                @foreach($user->roles as $role)
                                    <span class="role-badge role-badge role-badge--{{ $role->name }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>

                            <td class="text-center">
                                <div class="users-actions">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="cbtn cbtn--ghost-primary cbtn--xs">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                    <button type="button"
                                            class="cbtn cbtn--ghost-danger cbtn--xs"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-delete-url="{{ route('admin.users.destroy', $user) }}">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="5">
                                <div class="dash-empty py-4">
                                    <i class="bi bi-people dash-empty__icon"></i>
                                    <span>No users found.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- No search results state — shown by JS --}}
        <div class="dash-empty py-4 d-none" id="noResults">
            <i class="bi bi-search dash-empty__icon"></i>
            <span>No users match your search.<br>
                <small>Try a different name, email, or role.</small>
            </span>
        </div>

    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="dash-card__body border-top d-flex justify-content-center py-3">
            {{ $users->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif

</div>

{{-- ============================================================
     Delete Confirmation Modal
============================================================ --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content users-delete-modal">

            <div class="modal-body text-center p-4">
                <div class="users-delete-modal__icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="users-delete-modal__title">Delete User?</h6>
                <p class="users-delete-modal__message">
                    You are about to delete <strong id="deleteUserName"></strong>.
                    This action cannot be undone.
                </p>
            </div>

            <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger px-4">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/admin/users.js')
@endpush
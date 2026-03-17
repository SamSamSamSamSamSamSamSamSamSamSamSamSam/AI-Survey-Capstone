@extends('layouts.default')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Users</h5>
                    <div class="d-flex align-items-center w-50">
                        <div class="col-4 me-3">
                            <select id="roleFilter" class="form-select form-select-sm me-2">
                                <option value="all">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input 
                            type="text" 
                            id="userSearch" 
                            class="form-control form-control-sm" 
                            placeholder="Search users by name or email..."
                        >
                    </div>

                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-success btn-sm ms-3">
                        <i class="bi bi-journal-text me-1"></i> Manage Courses
                    </a>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th class="text-center" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="user-row">
                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                        <td class="user-name fw-semibold">{{ $user->name }}</td>
                                        <td class="user-email">{{ $user->email }}</td>
                                        <td class="user-roles" data-roles="{{ $user->roles->pluck('name')->toJson() }}">
                                            @foreach($user->roles as $role)
                                                <span class="badge rounded-pill bg-secondary">{{ ucfirst($role->name) }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="post" style="display:inline" onsubmit="return confirm('Delete user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-center mt-2">
                        {{ $users->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/users.js') }}"></script>
@endpush

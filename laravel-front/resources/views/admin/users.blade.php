@extends('layouts.default')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Users</h5>
                    <div class="d-flex w-50">
                        <input 
                            type="text" 
                            id="userSearch" 
                            class="form-control form-control-sm" 
                            placeholder="Search users by name or email..."
                        >
                    </div>
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
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge rounded-pill bg-secondary">{{ ucfirst($role->name) }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="#" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
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

                <!-- Pagination -->
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-center mt-2">
                        {{ $users->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const rows = document.querySelectorAll('#usersTable .user-row');

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();

        rows.forEach(row => {
            const name = row.querySelector('.user-name').textContent.toLowerCase();
            const email = row.querySelector('.user-email').textContent.toLowerCase();
            const matches = name.includes(term) || email.includes(term);

            row.style.display = matches ? '' : 'none';
        });
    });
});
</script>
@endsection

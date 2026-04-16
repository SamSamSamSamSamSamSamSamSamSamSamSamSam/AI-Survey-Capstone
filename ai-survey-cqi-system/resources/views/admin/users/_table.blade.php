@if ($users->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-people"></i></div>
        <p class="empty-state-text">No users found.</p>
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
                            <div class="user-avatar-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                            <span class="fw-500">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="text-muted-sm">{{ $user->email }}</td>
                    <td>
                        @forelse ($user->roles as $role)
                            <span class="role-pill role-pill--{{ $role->name }}">{{ ucfirst($role->name) }}</span>
                        @empty
                            <span class="role-pill role-pill--none">No Role</span>
                        @endforelse
                    </td>
                    <td>
                        <span class="verified-badge verified-badge--{{ $user->email_verified_at ? 'yes' : 'no' }}">
                            <i class="bi bi-{{ $user->email_verified_at ? 'check-circle-fill' : 'dash-circle' }}"></i>
                            {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                        </span>
                    </td>
                    <td>
                        <span class="status-pill status-pill--{{ $user->trashed() ? 'inactive' : 'active' }}">
                            {{ $user->trashed() ? 'Deactivated' : 'Active' }}
                        </span>
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
        <div class="table-pagination mt-3">
            {{ $users->links() }}
        </div>
    @endif
@endif
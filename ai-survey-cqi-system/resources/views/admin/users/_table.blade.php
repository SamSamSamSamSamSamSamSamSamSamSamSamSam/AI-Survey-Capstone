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
                        {{-- Keep your existing table-actions div here --}}
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
@extends('layouts.app')
@section('title', 'User Details')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">User Details</h2>
        <p class="page-subheading">Viewing profile for <strong>{{ $user->name }}</strong></p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
</div>

<div class="show-page-layout">

    {{-- ===== LEFT: Profile card ===== --}}
    <div class="show-profile-card">

        {{-- Avatar --}}
        <div class="profile-avatar">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>

        <h3 class="profile-name">{{ $user->name }}</h3>
        <p class="profile-id">{{ $user->user_id_number }}</p>

        <div class="profile-roles">
            @forelse ($user->roles as $role)
                <span class="role-pill role-pill--{{ $role->name }}">{{ ucfirst($role->name) }}</span>
            @empty
                <span class="role-pill role-pill--none">No Role</span>
            @endforelse
        </div>

        <div class="profile-status mt-3">
            @if ($user->trashed())
                <span class="status-pill status-pill--inactive">
                    <i class="bi bi-x-circle me-1"></i>Deactivated
                </span>
            @else
                <span class="status-pill status-pill--active">
                    <i class="bi bi-check-circle me-1"></i>Active
                </span>
            @endif
        </div>

    </div>

    {{-- ===== RIGHT: Details + Actions ===== --}}
    <div class="show-details-col">

        {{-- Detail card --}}
        <div class="card mb-3">
            <div class="card-body p-0">

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bi bi-person-badge me-2 text-muted"></i>ID Number
                    </span>
                    <span class="detail-value text-mono">{{ $user->user_id_number }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bi bi-person me-2 text-muted"></i>Full Name
                    </span>
                    <span class="detail-value">{{ $user->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bi bi-envelope me-2 text-muted"></i>Email
                    </span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bi bi-shield-check me-2 text-muted"></i>Email Verified
                    </span>
                    <span class="detail-value">
                        @if ($user->email_verified_at)
                            <span class="verified-badge verified-badge--yes">
                                <i class="bi bi-check-circle-fill"></i>
                                {{ $user->email_verified_at->format('M d, Y h:i A') }}
                            </span>
                        @else
                            <span class="verified-badge verified-badge--no">
                                <i class="bi bi-dash-circle"></i> Not verified
                            </span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bi bi-calendar me-2 text-muted"></i>Created
                    </span>
                    <span class="detail-value text-muted-sm">
                        {{ $user->created_at->format('M d, Y h:i A') }}
                    </span>
                </div>

                <div class="detail-row detail-row--last">
                    <span class="detail-label">
                        <i class="bi bi-clock-history me-2 text-muted"></i>Last Updated
                    </span>
                    <span class="detail-value text-muted-sm">
                        {{ $user->updated_at->format('M d, Y h:i A') }}
                    </span>
                </div>

            </div>
        </div>

        {{-- Actions card --}}
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Actions</h6>
                <div class="d-flex flex-wrap gap-2">

                    @if (! $user->trashed())
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i> Edit User
                        </a>

                        <form method="POST"
                              action="{{ route('admin.users.reset-password', $user->id) }}"
                              data-confirm="Send a new password to {{ $user->email }}?">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="bi bi-key me-1"></i> Reset Password
                            </button>
                        </form>

                        @if ($user->id !== auth()->id())
                            <form method="POST"
                                  action="{{ route('admin.users.destroy', $user->id) }}"
                                  data-confirm="Deactivate {{ $user->name }}? They will lose system access.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-person-dash me-1"></i> Deactivate
                                </button>
                            </form>
                        @endif

                    @else
                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-person-check me-1"></i> Restore User
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        </div>

    </div>{{-- /.show-details-col --}}

</div>{{-- /.show-page-layout --}}

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
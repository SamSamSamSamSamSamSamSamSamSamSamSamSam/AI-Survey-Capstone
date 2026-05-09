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
        <h2 class="page-heading">User Profile</h2>
        <p class="page-subheading">Account Profile & System Activity</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="show-page-layout">

    {{-- ===== LEFT: Profile card ===== --}}
    <div class="show-profile-card text-center p-3">
        {{-- Role-Based Icon and Color Logic --}}
        @php
            $roleData = match(true) {
                $user->hasRole('admin') => ['icon' => 'bi-shield-lock', 'color' => 'danger', 'label' => 'System Administrator'],
                $user->hasRole('faculty') => ['icon' => 'bi-mortarboard', 'color' => 'primary', 'label' => 'Faculty Member'],
                $user->hasRole('student') => ['icon' => 'bi-person-video3', 'color' => 'success', 'label' => 'Student'],
                default => ['icon' => 'bi-person-circle', 'color' => 'secondary', 'label' => 'User'],
            };
        @endphp

        <div class="mb-3">
            <i class="bi {{ $roleData['icon'] }} text-{{ $roleData['color'] }}" style="font-size: 3rem;"></i>
        </div>

        <h5 class="mb-1 fw-bold">{{ $user->name }}</h5>
        
        {{-- Displaying the Role Name prominently --}}
        <p class="text-{{ $roleData['color'] }} fw-semibold small mb-2 uppercase-tracking">
            {{ $roleData['label'] }}
        </p>
        
        <p class="text-muted small mb-3">ID: {{ $user->user_id_number }}</p>

        <div class="mt-2">
            @if ($user->trashed())
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle w-100 py-2">
                    Inactive
                </span>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle w-100 py-2">
                    Active
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
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted mb-3 small text-uppercase fw-bold">Account Management</h6>
                <div class="d-flex flex-wrap gap-2">
                    @if (! $user->trashed())
                        {{-- Primary Action --}}
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </a>

                        {{-- Support Action --}}
                        <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}" data-confirm="Reset password for {{ $user->name }}?">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning btn-sm px-3">
                                <i class="bi bi-shield-lock me-1"></i> Reset Password
                            </button>
                        </form>

                        {{-- Danger Action --}}
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" data-confirm="Deactivate {{ $user->name }}?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                    <i class="bi bi-person-x me-1"></i> Deactivate
                                </button>
                            </form>
                        @endif
                    @else
                        {{-- Success Action --}}
                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm px-4">
                                <i class="bi bi-person-check me-1"></i> Restore Account access
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
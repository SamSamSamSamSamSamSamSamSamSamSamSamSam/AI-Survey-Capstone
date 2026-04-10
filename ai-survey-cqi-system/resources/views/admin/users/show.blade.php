@extends('admin.layouts.app')
@section('title', 'User Details')

@push('styles')
<style>
        /* *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f3f4f6; color: #111; padding: 2rem; } */
        .page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.3rem; }
        .back { font-size: .875rem; color: #4f46e5; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .card { background: #fff; border-radius: 8px; padding: 2rem; max-width: 600px; box-shadow: 0 1px 6px rgba(0,0,0,.07); }
        .detail-row { display: flex; padding: .65rem 0; border-bottom: 1px solid #f3f4f6; font-size: .9rem; }
        .detail-row:last-of-type { border-bottom: none; }
        .detail-label { width: 160px; flex-shrink: 0; color: #6b7280; font-weight: 500; }
        .detail-value { flex: 1; color: #111; }
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 600; margin-right: .3rem; }
        .badge-admin   { background: #fee2e2; color: #dc2626; }
        .badge-faculty { background: #dbeafe; color: #1d4ed8; }
        .badge-student { background: #d1fae5; color: #065f46; }
        .badge-default { background: #f3f4f6; color: #374151; }
        .actions { display: flex; gap: .65rem; flex-wrap: wrap; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f3f4f6; }
        .btn { padding: .5rem 1.1rem; border-radius: 6px; font-size: .875rem; cursor: pointer; text-decoration: none; border: none; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-warning { background: #fef3c7; color: #92400e; }
        .btn-warning:hover { background: #fde68a; }
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #fecaca; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 6px; padding: .75rem 1rem; margin-bottom: 1rem; font-size: .875rem; }
    </style>
@endpush

@section('content')
<div class="page-header">
    <a href="{{ route('admin.users.index') }}" class="back">← Back to Users</a>
    <h1>User Details</h1>
</div>

@if (session('success'))
    <div class="alert-success" style="max-width:600px; margin-bottom:1.25rem;">{{ session('success') }}</div>
@endif

<div class="card">

    <div class="detail-row">
        <span class="detail-label">ID Number</span>
        <span class="detail-value">{{ $user->user_id_number }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Full Name</span>
        <span class="detail-value">{{ $user->name }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Email</span>
        <span class="detail-value">{{ $user->email }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Roles</span>
        <span class="detail-value">
            @forelse ($user->roles as $role)
                <span class="badge badge-{{ $role->name }}">{{ ucfirst($role->name) }}</span>
            @empty
                <span class="badge badge-default">No Role</span>
            @endforelse
        </span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Email Verified</span>
        <span class="detail-value">
            {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y h:i A') : 'Not verified' }}
        </span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Status</span>
        <span class="detail-value">{{ $user->trashed() ? 'Deactivated' : 'Active' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Created</span>
        <span class="detail-value">{{ $user->created_at->format('M d, Y h:i A') }}</span>
    </div>

    <div class="actions">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">Edit</a>

        {{-- Reset Password --}}
        <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}" onsubmit="return confirm('Send a new password to {{ $user->email }}?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-warning">Reset Password</button>
        </form>

        {{-- Soft Delete --}}
        @if (! $user->trashed() && $user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Deactivate this user?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Deactivate</button>
            </form>
        @endif
    </div>

</div>
@endsection


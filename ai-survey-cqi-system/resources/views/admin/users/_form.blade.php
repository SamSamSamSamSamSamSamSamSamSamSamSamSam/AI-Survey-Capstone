{{-- ============================================================
     partials/_form.blade.php  (admin/users)
     Shared by create.blade.php and edit.blade.php
     $user is null on create, populated on edit
     ============================================================ --}}

{{-- Info notice (create only) --}}
@isset($isCreate)
<div class="info-notice mb-4">
    <i class="bi bi-info-circle-fill info-notice__icon"></i>
    <div>
        A verirification link will be send to the user's email address after creating the account. 
        The user must click the link to set their password and activate their account.
    </div>
</div>
@endisset

{{-- ID Number --}}
<div class="mb-4">
    <label class="form-label" for="user_id_number">
        ID Number <span class="text-danger">*</span>
    </label>
    {{-- <p class="form-text mt-0 mb-1">e.g., 20101234</p> --}}
    <input
        type="text"
        id="user_id_number"
        name="user_id_number"
        class="form-control @error('user_id_number') is-invalid @enderror"
        value="{{ old('user_id_number', $user->user_id_number ?? '') }}"
        placeholder="20100000"
        required
    >
    @error('user_id_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Full Name --}}
<div class="mb-4">
    <label class="form-label" for="name">
        Full Name <span class="text-danger">*</span>
    </label>
    <input
        type="text"
        id="name"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $user->name ?? '') }}"
        placeholder="Juan dela Cruz"
        required
    >
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Email --}}
<div class="mb-4">
    <label class="form-label" for="email">
        Email Address <span class="text-danger">*</span>
    </label>
    <input
        type="email"
        id="email"
        name="email"
        class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $user->email ?? '') }}"
        placeholder="juan@usc.edu.ph"
        required
    >
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Roles --}}
<div class="mb-4">
    <label class="form-label">
        Roles <span class="text-danger">*</span>
    </label>
    <div class="roles-grid">
        @foreach ($roles as $role)
            @php
                $checked = in_array(
                    $role->id,
                    old('roles', isset($user) ? $user->roles->pluck('id')->toArray() : [])
                );
            @endphp
            <label class="role-checkbox">
                <input
                    type="radio"
                    name="roles[]"
                    value="{{ $role->id }}"
                    class="role-checkbox__input"
                    {{ $checked ? 'checked' : '' }}
                >
                <span class="role-checkbox__label role-checkbox__label--{{ $role->name }}">
                    <i class="bi bi-person-badge me-1"></i>{{ ucfirst($role->name) }}
                </span>
            </label>
        @endforeach
    </div>
    @error('roles')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

{{-- Actions --}}
<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        @isset($isCreate)
            <i class="bi bi-person-plus me-1"></i> Create User
        @else
            <i class="bi bi-check-lg me-1"></i> Save Changes
        @endisset
    </button>
    <a href="{{ isset($user) ? route('admin.users.show', $user->id) : route('admin.users.index') }}"
       class="btn btn-outline-secondary">
        Cancel
    </a>
</div>
@extends('layouts.default')

@section('content')
<div class="container py-4">
    <h3>Edit User</h3>

    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <form action="{{ route('admin.users.update', $user) }}" method="post">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password (leave blank to keep)</label>
            <input name="password" type="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input name="password_confirmation" type="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Roles</label>
            <div>
                @foreach($roles as $role)
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}"
                            {{ $user->roles->contains('id', $role->id) ? 'checked' : '' }}>
                        <span class="form-check-label">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
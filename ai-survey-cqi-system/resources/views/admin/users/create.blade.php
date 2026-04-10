<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User — Admin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f3f4f6; color: #111; padding: 2rem; }
        .page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.3rem; }
        .back { font-size: .875rem; color: #4f46e5; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .card { background: #fff; border-radius: 8px; padding: 2rem; max-width: 600px; box-shadow: 0 1px 6px rgba(0,0,0,.07); }
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: .875rem; font-weight: 500; margin-bottom: .35rem; }
        .hint { font-size: .775rem; color: #9ca3af; margin-bottom: .35rem; }
        input[type=text], input[type=email] { width: 100%; padding: .55rem .75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .9rem; }
        input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        input.is-invalid { border-color: #dc2626; }
        .error { color: #dc2626; font-size: .8rem; margin-top: .3rem; }
        .roles-grid { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: .35rem; }
        .role-option { display: flex; align-items: center; gap: .4rem; cursor: pointer; }
        .role-option input { width: auto; cursor: pointer; }
        .role-option span { font-size: .9rem; }
        .form-actions { display: flex; gap: .75rem; margin-top: 1.5rem; }
        .btn { padding: .55rem 1.2rem; border-radius: 6px; font-size: .875rem; cursor: pointer; text-decoration: none; border: none; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .info-box { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 6px; padding: .75rem 1rem; font-size: .825rem; margin-bottom: 1.25rem; line-height: 1.5; }
    </style>
</head>
<body>

<div class="page-header">
    <a href="{{ route('admin.users.index') }}" class="back">← Back to Users</a>
    <h1>Create New User</h1>
</div>

<div class="card">

    <div class="info-box">
        A secure temporary password will be auto-generated and emailed to the user upon creation.
        The account will be marked as verified automatically.
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        {{-- ID Number --}}
        <div class="form-group">
            <label for="user_id_number">ID Number <span style="color:#dc2626">*</span></label>
            <p class="hint">Format: YYYY-NNNNN (e.g. 2024-00042)</p>
            <input
                type="text"
                id="user_id_number"
                name="user_id_number"
                value="{{ old('user_id_number') }}"
                class="{{ $errors->has('user_id_number') ? 'is-invalid' : '' }}"
                placeholder="2024-00001"
            >
            @error('user_id_number')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div class="form-group">
            <label for="name">Full Name <span style="color:#dc2626">*</span></label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                placeholder="Juan dela Cruz"
            >
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label for="email">Email Address <span style="color:#dc2626">*</span></label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="juan@example.com"
            >
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Roles --}}
        <div class="form-group">
            <label>Roles <span style="color:#dc2626">*</span></label>
            <div class="roles-grid">
                @foreach ($roles as $role)
                    <label class="role-option">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->id }}"
                            {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                        >
                        <span>{{ ucfirst($role->name) }}</span>
                    </label>
                @endforeach
            </div>
            @error('roles')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create User &amp; Send Credentials</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>

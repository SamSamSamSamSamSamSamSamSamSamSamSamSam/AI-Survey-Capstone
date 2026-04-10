@extends('admin.layouts.app')
@section('title', 'Create Program')

@section('content')
<div class="page-header">
    <h1>Create Program</h1>
    <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:520px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.programs.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Program Code <span style="color:#dc2626">*</span></label>
                <input type="text" name="program_code" class="form-control {{ $errors->has('program_code') ? 'is-invalid' : '' }}" value="{{ old('program_code') }}" placeholder="e.g. BSCS">
                @error('program_code') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Program Name <span style="color:#dc2626">*</span></label>
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" placeholder="e.g. Bachelor of Science in Computer Science">
                @error('name') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Program</button>
                <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

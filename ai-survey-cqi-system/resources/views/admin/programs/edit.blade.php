@extends('layouts.app')
@section('title', 'Edit Program')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program->id) }}">{{ $program->program_code }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Program</h2>
        <p class="page-subheading">
            Editing <strong>{{ $program->name }}</strong>
            <span class="program-code-badge ms-2">{{ $program->program_code }}</span>
        </p>
    </div>
    <a href="{{ route('admin.programs.show', $program->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Program
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.programs.update', $program->id) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.programs._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.programs.show', $program->id) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $program->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $program->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
@extends('layouts.app')
@section('title', 'Edit Curriculum')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.curricula.index') }}">Curricula</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.curricula.show', $curriculum->id) }}">{{ $curriculum->curriculum_code }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Curriculum</h2>
        <p class="page-subheading">
            Editing
            <strong>{{ $curriculum->curriculum_code }}</strong>
            <span class="program-code-badge ms-2">{{ $curriculum->program->program_code ?? '' }}</span>
        </p>
    </div>
    <a href="{{ route('admin.curricula.show', $curriculum->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Curriculum
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.curricula.update', $curriculum->id) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.curricula._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.curricula.show', $curriculum->id) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $curriculum->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $curriculum->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
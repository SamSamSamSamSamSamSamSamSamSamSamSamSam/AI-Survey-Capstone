@extends('layouts.app')
@section('title', 'Edit Subject')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Subject</h2>
        <p class="page-subheading">
            Editing
            <strong>{{ $subject->name }}</strong>
            <span class="program-code-badge program-code-badge--subject ms-2">{{ $subject->course_code }}</span>
        </p>
    </div>
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Subjects
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.subjects._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $subject->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $subject->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
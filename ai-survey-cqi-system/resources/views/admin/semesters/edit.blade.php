@extends('layouts.app')
@section('title', 'Edit Semester')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.semesters.index') }}">Semesters</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Semester</h2>
        <p class="page-subheading">
            Editing <strong>{{ $semester->full_label }}</strong>
        </p>
    </div>
    <a href="{{ route('admin.semesters.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Semesters
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">

        {{-- Read-only context info --}}
        <div class="alert alert-light border mb-4 d-flex gap-3" style="font-size:.875rem;">
            <div>
                <span class="text-muted">Academic Year</span><br>
                <strong>{{ $semester->academic_year_label }}</strong>
            </div>
            <div class="vr"></div>
            <div>
                <span class="text-muted">Semester</span><br>
                <strong>{{ $semester->semester_label }}</strong>
            </div>
            <div class="vr"></div>
            <div>
                <span class="text-muted">Status</span><br>
                @if ($semester->is_active)
                    <span class="status-pill status-pill--active">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </span>
                @else
                    <span class="status-pill status-pill--archived">Inactive</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.semesters.update', $semester->id) }}" novalidate>
            @csrf @method('PUT')

            @include('admin.semesters._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.semesters.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $semester->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $semester->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
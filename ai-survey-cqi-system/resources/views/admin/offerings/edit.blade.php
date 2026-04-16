@extends('layouts.app')
@section('title', 'Edit Course Offering')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.index') }}">Course Offerings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.show', $offering->id) }}">{{ $offering->display_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Course Offering</h2>
        <p class="page-subheading">
            Editing <strong>{{ $offering->subject->course_code }}</strong>
            · {{ $offering->teacher->name }}
        </p>
    </div>
    <a href="{{ route('admin.offerings.show', $offering->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Offering
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.offerings.update', $offering->id) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.offerings._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.offerings.show', $offering->id) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $offering->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $offering->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
@extends('layouts.app')
@section('title', 'Create Course Offering')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.index') }}">Course Offerings</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Create Course Offering</h2>
        <p class="page-subheading">Assign a subject and faculty member to a semester.</p>
    </div>
    <a href="{{ route('admin.offerings.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Offerings
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.offerings.store') }}" novalidate>
            @csrf
            @include('admin.offerings._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Offering
                </button>
                <a href="{{ route('admin.offerings.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
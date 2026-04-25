@extends('layouts.app')
@section('title', 'Create Program')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programs</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Create Program</h2>
        <p class="page-subheading">Add a new academic program to the system.</p>
    </div>
    <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Programs
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul style="list-style-type: none;">
            @foreach ($errors->all() as $error)
                <li><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.programs.store') }}" novalidate>
            @csrf
            @include('admin.programs._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Program
                </button>
                <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
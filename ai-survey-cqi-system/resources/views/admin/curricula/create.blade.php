@extends('layouts.app')
@section('title', 'Create Curriculum')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.curricula.index') }}">Curricula</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Create Curriculum</h2>
        <p class="page-subheading">Define a new curriculum version for an academic program.</p>
    </div>
    <a href="{{ route('admin.curricula.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Curricula
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.curricula.store') }}" novalidate>
            @csrf
            @include('admin.curricula._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Curriculum
                </button>
                <a href="{{ route('admin.curricula.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
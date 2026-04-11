@extends('layouts.app')
@section('title', 'Edit Category')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.question-categories.index') }}">Question Categories</a></li>
    <li class="breadcrumb-item active">Edit Category</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Category</h2>
        <p class="page-subheading">Editing <strong>{{ $questionCategory->name }}</strong></p>
    </div>
    <a href="{{ route('admin.question-categories.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.question-categories.update', $questionCategory->id) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.question-categories._form')
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.question-categories.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $questionCategory->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $questionCategory->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>
@endsection

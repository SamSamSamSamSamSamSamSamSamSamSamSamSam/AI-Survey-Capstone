@extends('layouts.app')
@section('title', 'Edit Survey')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.show', $survey->id) }}">{{ Str::limit($survey->title, 30) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Survey</h2>
        <p class="page-subheading">Editing <strong>{{ $survey->title }}</strong></p>
    </div>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Survey
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.surveys.update', $survey->id) }}" novalidate>
            @csrf @method('PUT')

            @include('admin.surveys._form')

            <div class="form-actions mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>

        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $survey->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $survey->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
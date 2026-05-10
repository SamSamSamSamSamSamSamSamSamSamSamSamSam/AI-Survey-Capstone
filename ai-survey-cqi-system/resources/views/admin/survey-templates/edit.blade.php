@extends('layouts.app')
@section('title', 'Edit Template')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.survey-templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.survey-templates.show', $surveyTemplate->id) }}">{{ Str::limit($surveyTemplate->name, 30) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Modify Template</h2>
        <p class="page-subheading">Editing <strong>{{ $surveyTemplate->name }}</strong></p>
    </div>
    <a href="{{ route('admin.survey-templates.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Template
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.survey-templates.update', $surveyTemplate->id) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.survey-templates._form')
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.survey-templates.show', $surveyTemplate->id) }}" class="btn btn-outline-secondary">
                    Manage Questions
                </a>
            </div>
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $surveyTemplate->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $surveyTemplate->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
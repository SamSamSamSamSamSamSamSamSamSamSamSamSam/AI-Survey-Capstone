@extends('layouts.app')
@section('title', 'Add Question')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.show', $survey->id) }}">{{ $survey->title }}</a></li>
    <li class="breadcrumb-item active">Add Question</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-heading">Add Question</h2>
        <p class="page-subheading">Create a new question for the survey.</p>
    </div>
</div>

<div class="form-page-layout">

    <div class="alert alert-info" style="max-width:600px;">Survey: <strong>{{ $survey->title }}</strong></div>

    <div class="form-card" style="max-width:600px;">
        <form method="POST" action="{{ route('admin.surveys.questions.store', $survey->id) }}" novalidate>
            @csrf
            @include('admin.surveys.questions._form')
            <div class="form-actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Question
                </button>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
</div>
@endsection

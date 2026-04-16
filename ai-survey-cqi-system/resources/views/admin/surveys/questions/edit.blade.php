@extends('layouts.app')
@section('title', 'Edit Question')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.show', $survey->id) }}">{{ $survey->title }}</a></li>
    <li class="breadcrumb-item active">Edit Question</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Question</h2>
        <p class="page-subheading">Edit this question from the survey</p>
    </div>
</div>

<div class="form-page-layout">
    <div class="alert alert-info" style="max-width:600px;">Survey: <strong>{{ $survey->title }}</strong></div>
    <div class="form-card">
        <form method="POST" action="{{ route('admin.surveys.questions.update', [$survey->id, $question->id]) }}">
            @csrf @method('PUT')
            @include('admin.surveys.questions._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

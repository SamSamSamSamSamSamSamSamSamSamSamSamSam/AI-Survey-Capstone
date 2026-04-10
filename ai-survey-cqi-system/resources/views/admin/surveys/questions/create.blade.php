@extends('admin.layouts.app')
@section('title', 'Add Question')
@section('content')
<div class="page-header">
    <h1>Add Question</h1>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">← Back to Survey</a>
</div>
<div class="alert alert-info" style="max-width:600px;">Survey: <strong>{{ $survey->title }}</strong></div>
<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.surveys.questions.store', $survey->id) }}">
            @csrf
            @include('admin.surveys.questions._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Add Question</button>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

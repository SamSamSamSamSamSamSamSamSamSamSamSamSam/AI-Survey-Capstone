@extends('admin.layouts.app')
@section('title', 'Edit Question')
@section('content')
<div class="page-header">
    <h1>Edit Question</h1>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">← Back to Survey</a>
</div>
<div class="alert alert-info" style="max-width:600px;">Survey: <strong>{{ $survey->title }}</strong></div>
<div class="card" style="max-width:600px;">
    <div class="card-body">
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

@extends('admin.layouts.app')
@section('title', 'Edit Template')
@section('content')
<div class="page-header">
    <h1>Edit Template</h1>
    <a href="{{ route('admin.survey-templates.show', $surveyTemplate->id) }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.survey-templates.update', $surveyTemplate->id) }}">
            @csrf @method('PUT')
            @include('admin.survey-templates._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.survey-templates.show', $surveyTemplate->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

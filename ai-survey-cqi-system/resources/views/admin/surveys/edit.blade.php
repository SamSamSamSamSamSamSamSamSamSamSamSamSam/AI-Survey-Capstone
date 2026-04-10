@extends('admin.layouts.app')
@section('title', 'Edit Survey')
@section('content')
<div class="page-header">
    <h1>Edit Survey</h1>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.surveys.update', $survey->id) }}">
            @csrf @method('PUT')
            @include('admin.surveys._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

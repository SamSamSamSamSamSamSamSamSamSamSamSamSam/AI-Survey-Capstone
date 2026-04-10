@extends('admin.layouts.app')
@section('title', 'Create Survey Template')
@section('content')
<div class="page-header">
    <h1>Create Survey Template</h1>
    <a href="{{ route('admin.survey-templates.index') }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.survey-templates.store') }}">
            @csrf
            @include('admin.survey-templates._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Template</button>
                <a href="{{ route('admin.survey-templates.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

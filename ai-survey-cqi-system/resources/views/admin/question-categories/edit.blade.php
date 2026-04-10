@extends('admin.layouts.app')
@section('title', 'Edit Category')
@section('content')
<div class="page-header">
    <h1>Edit Category</h1>
    <a href="{{ route('admin.question-categories.index') }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:500px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.question-categories.update', $questionCategory->id) }}">
            @csrf @method('PUT')
            @include('admin.question-categories._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.question-categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

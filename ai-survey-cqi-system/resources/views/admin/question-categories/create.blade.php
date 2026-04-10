{{-- resources/views/admin/question-categories/create.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'New Category')
@section('content')
<div class="page-header">
    <h1>New Question Category</h1>
    <a href="{{ route('admin.question-categories.index') }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:500px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.question-categories.store') }}">
            @csrf
            @include('admin.question-categories._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Category</button>
                <a href="{{ route('admin.question-categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

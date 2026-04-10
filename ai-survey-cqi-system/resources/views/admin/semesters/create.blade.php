{{-- resources/views/admin/semesters/create.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Create Semester')

@section('content')
<div class="page-header">
    <h1>Create Semester</h1>
    <a href="{{ route('admin.semesters.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:480px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.semesters.store') }}">
            @csrf
            @include('admin.semesters._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Semester</button>
                <a href="{{ route('admin.semesters.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

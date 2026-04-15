@extends('layouts.app')
@section('title', 'Edit Subject')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Edit Subject</h1>
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}">
            @csrf @method('PUT')
            @include('admin.subjects._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

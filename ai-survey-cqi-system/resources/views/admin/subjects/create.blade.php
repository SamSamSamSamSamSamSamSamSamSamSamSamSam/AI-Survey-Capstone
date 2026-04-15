@extends('layouts.app')
@section('title', 'Create Subject')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Create Subject</h1>
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            @include('admin.subjects._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Subject</button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

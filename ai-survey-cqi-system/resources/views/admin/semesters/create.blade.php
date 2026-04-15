{{-- resources/views/admin/semesters/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Create Semester')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.semesters.index') }}">Semesters</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

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

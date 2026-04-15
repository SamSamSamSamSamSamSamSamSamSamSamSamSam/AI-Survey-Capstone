@extends('layouts.app')
@section('title', 'Edit Curriculum')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.curricula.index') }}">Curricula</a></li>
    <li class="breadcrumb-item active">Edit {{ $curriculum->curriculum_code }}</li>
</ol>
@endsection


@section('content')
<div class="page-header">
    <h1>Edit Curriculum</h1>
    <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:580px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.curricula.update', $curriculum->id) }}">
            @csrf @method('PUT')
            @include('admin.curricula._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.curricula.show', $curriculum->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

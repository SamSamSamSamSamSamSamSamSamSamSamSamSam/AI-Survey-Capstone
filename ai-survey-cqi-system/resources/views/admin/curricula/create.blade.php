@extends('admin.layouts.app')
@section('title', 'Create Curriculum')

@section('content')
<div class="page-header">
    <h1>Create Curriculum</h1>
    <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:580px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.curricula.store') }}">
            @csrf
            @include('admin.curricula._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Curriculum</button>
                <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

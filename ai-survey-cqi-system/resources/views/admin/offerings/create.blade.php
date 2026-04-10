{{-- resources/views/admin/offerings/create.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Create Course Offering')
@section('content')
<div class="page-header">
    <h1>Create Course Offering</h1>
    <a href="{{ route('admin.offerings.index') }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.offerings.store') }}">
            @csrf
            @include('admin.offerings._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Offering</button>
                <a href="{{ route('admin.offerings.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

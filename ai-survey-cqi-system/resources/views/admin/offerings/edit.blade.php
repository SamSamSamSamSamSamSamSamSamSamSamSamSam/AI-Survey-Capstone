@extends('admin.layouts.app')
@section('title', 'Edit Course Offering')
@section('content')
<div class="page-header">
    <h1>Edit Course Offering</h1>
    <a href="{{ route('admin.offerings.index') }}" class="btn btn-secondary">← Back</a>
</div>
<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.offerings.update', $offering->id) }}">
            @csrf @method('PUT')
            @include('admin.offerings._form')
            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.offerings.show', $offering->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Edit User')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Update Account</h2>
        <p class="page-subheading">Modify details for {{ $user->name }} (ID: {{ $user->user_id_number }})
    </div>
    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to User
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.users._form')
        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $user->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $user->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>

@endsection
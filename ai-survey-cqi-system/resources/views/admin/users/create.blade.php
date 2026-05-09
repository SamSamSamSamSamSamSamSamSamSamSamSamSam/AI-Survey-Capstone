@extends('layouts.app')
@section('title', 'Create User')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">User Registration</h2>
        <p class="page-subheading">Fill in the details to onboard a new member. Verification link will be sent automatically.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
            @csrf
            @include('admin.users._form', ['isCreate' => true])
        </form>
    </div>
</div>

@endsection
@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Users Management</h2>
        <a href="#" class="btn btn-primary">
            <i class="fa fa-user-plus me-1"></i> Add New User
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Users Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Sample rows for UI preview --}}
                    <tr>
                        <td>1</td>
                        <td>Juan Dela Cruz</td>
                        <td>juan@example.com</td>
                        <td><span class="badge bg-primary">Admin</span></td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-info text-white">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Maria Santos</td>
                        <td>maria@example.com</td>
                        <td><span class="badge bg-secondary">Faculty</span></td>
                        <td><span class="badge bg-danger">Inactive</span></td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-info text-white">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Carlos Reyes</td>
                        <td>carlos@example.com</td>
                        <td><span class="badge bg-success">Student</span></td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-info text-white">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

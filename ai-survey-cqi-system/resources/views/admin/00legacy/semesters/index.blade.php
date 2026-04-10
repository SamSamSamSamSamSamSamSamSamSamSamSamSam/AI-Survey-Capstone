@extends('layouts.default')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Semester Management</h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Create Semester Form --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Add New Semester</div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger small">
                            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                    @endif
                    <form action="{{ route('admin.semesters.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="academic_year"
                                   class="form-control form-control-sm"
                                   placeholder="e.g. 2024-2025"
                                   pattern="\d{4}-\d{4}"
                                   value="{{ old('academic_year') }}"
                                   required>
                            <div class="form-text">Format: YYYY-YYYY</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                            <select name="semester_number" class="form-select form-select-sm" required>
                                <option value="">-- Select --</option>
                                <option value="1" {{ old('semester_number') == '1' ? 'selected' : '' }}>1st Semester</option>
                                <option value="2" {{ old('semester_number') == '2' ? 'selected' : '' }}>2nd Semester</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-plus-circle me-1"></i> Create Semester
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Semesters List --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">All Semesters</div>
                <div class="card-body p-0">
                    @if($semesters->isEmpty())
                        <div class="p-4 text-center text-muted">No semesters created yet.</div>
                    @else
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Semester</th>
                                    <th>Academic Year</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($semesters as $semester)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $semester->name }}</td>
                                        <td>{{ $semester->academic_year }}</td>
                                        <td>
                                            @if($semester->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if(!$semester->is_active)
                                                <form action="{{ route('admin.semesters.activate', $semester) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success" title="Set as Active">
                                                        <i class="bi bi-check-circle me-1"></i> Set Active
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.semesters.destroy', $semester) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete {{ $semester->name }}? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Currently active</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
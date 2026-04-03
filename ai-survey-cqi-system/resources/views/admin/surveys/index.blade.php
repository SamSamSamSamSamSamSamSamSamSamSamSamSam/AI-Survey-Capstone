@extends('layouts.default')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary me-2">
            <i class="bi bi-plus-circle me-1"></i> Create Survey
        </a>
        <h2 class="fw-bold">Survey Management</h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Active Semester Info --}}
    @if($activeSemester)
        <div class="alert alert-info py-2 mb-3">
            <i class="bi bi-calendar2-range me-1"></i>
            Active Semester: <strong>{{ $activeSemester->name }}</strong> — 
            Duplicated surveys will be assigned to this semester.
        </div>
    @else
        <div class="alert alert-warning py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            No active semester set. Duplicated surveys will have no semester assigned.
            <a href="{{ route('admin.semesters.index') }}">Set one here.</a>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Survey List --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-muted">All Surveys</h5>
        </div>

        <div class="card-body p-0">
            @if($surveys->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 ps-4">#</th>
                                <th class="py-3">Title</th>
                                <th class="py-3">Evaluatee</th>
                                <th class="py-3">Target</th>
                                <th class="py-3">Course</th>
                                <th class="py-3">Semester</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Created</th>
                                <th class="py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surveys as $index => $survey)
                                <tr>
                                    <td class="align-middle ps-4">{{ $index + 1 }}</td>
                                    <td class="align-middle fw-semibold">{{ $survey->title }}</td>
                                    <td class="align-middle">{{ $survey->evaluatee->name }}</td>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill bg-info text-capitalize">{{ $survey->target_role }}</span>
                                    </td>
                                    <td class="align-middle">
                                        @if($survey->subject)
                                            <span class="badge rounded-pill bg-secondary">
                                                @if($survey->group) {{ $survey->group }} - @endif
                                                {{ $survey->subject->course_code }}
                                            </span>
                                        @elseif($survey->group)
                                            <span class="badge rounded-pill bg-info">{{ $survey->group }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($survey->semester)
                                            <span class="badge rounded-pill bg-primary">{{ $survey->semester->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($survey->is_active)
                                            <span class="badge rounded-pill bg-success">Active</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-muted">{{ $survey->created_at->format('M d, Y') }}</td>
                                    <td class="align-middle text-end pe-4">
                                        <div class="btn-group" role="group">

                                            {{-- View --}}
                                            <a href="{{ route('admin.surveys.show', $survey->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip" title="View Survey">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            {{-- Edit --}}
                                            <a href="{{ route('admin.surveys.edit', $survey->id) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               data-bs-toggle="tooltip" title="Edit Survey">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            {{-- Duplicate --}}
                                            <form action="{{ route('admin.surveys.duplicate', $survey->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="tooltip"
                                                        title="Duplicate to active semester"
                                                        onclick="return confirm('Duplicate \'{{ $survey->title }}\' into the active semester?')">
                                                    <i class="bi bi-copy"></i>
                                                </button>
                                            </form>

                                            {{-- Activate/Deactivate --}}
                                            <form action="{{ route('admin.surveys.toggle-status', $survey->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-{{ $survey->is_active ? 'warning' : 'success' }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $survey->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fa {{ $survey->is_active ? 'fa-times' : 'fa-check' }}"></i>
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form action="{{ route('admin.surveys.destroy', $survey->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this survey?')"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="tooltip" title="Delete Survey">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                    <h4 class="mb-1">No Surveys Found</h4>
                    <p class="mb-0">Start by creating a new one to see it listed here.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/surveys/index.js') }}"></script>
@endpush
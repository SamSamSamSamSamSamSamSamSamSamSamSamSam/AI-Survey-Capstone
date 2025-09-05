@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">My Surveys</h2>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Create New Survey
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Survey List --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($surveys->count() > 0)
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveys as $index => $survey)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $survey->title }}</td>
                                <td>{{ Str::limit($survey->description, 50) }}</td>
                                <td>{{ $survey->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.surveys.edit', $survey->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.surveys.destroy', $survey->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this survey?')" 
                                                class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    <i class="fa fa-folder-open fa-2x mb-2"></i>
                    <p class="mb-0">No surveys found. Start by creating a new one.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

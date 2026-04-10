@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">{{ $survey->title }}</h2>
            <p class="text-muted mb-0">{{ $survey->description }}</p>
        </div>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Surveys
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Survey Questions</h5>
                </div>
                <div class="card-body">
                    @foreach($survey->questions as $index => $question)
                        <div class="mb-3 p-3 border rounded">
                            <h6 class="mb-1">Question {{ $index + 1 }}</h6>
                            <p class="mb-2">{{ $question->question_text }}</p>
                            <small class="text-muted">Type: {{ ucfirst($question->type) }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Survey Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Evaluatee:</strong>
                        <span class="ms-2">
                            {{ $survey->evaluatee->name ?? 'N/A' }}
                            @if($survey->evaluatee && $survey->evaluatee->roles->isNotEmpty())
                                ({{ ucfirst($survey->evaluatee->roles->first()->name) }})
                            @endif
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Target Audience:</strong>
                        <span class="badge bg-info text-capitalize ms-2">{{ $survey->target_role }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Course:</strong>
                        <span class="badge bg-info text-capitalize ms-2">{{ $survey->subject->course_code ?? 'N/A'}}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        @if($survey->is_active)
                            <span class="badge bg-success ms-2">Active</span>
                        @else
                            <span class="badge bg-secondary ms-2">Inactive</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Created By:</strong>
                        <span class="ms-2">{{ $survey->creator->name }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Created On:</strong>
                        <span class="ms-2">{{ $survey->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong>
                        <span class="ms-2">{{ $survey->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.surveys.edit', $survey->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit me-1"></i> Edit Survey
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
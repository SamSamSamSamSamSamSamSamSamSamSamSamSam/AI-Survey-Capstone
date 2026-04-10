@extends('layouts.default')

@section('content')
<div class="container mt-4">

    {{-- Header + Semester Badge --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Available Surveys</h1>
        @if($activeSemester)
            <span class="badge bg-primary fs-6 px-3 py-2">
                <i class="bi bi-calendar2-range me-1"></i>
                {{ $activeSemester->name }}
            </span>
        @else
            <span class="badge bg-secondary fs-6 px-3 py-2">
                <i class="bi bi-calendar2-x me-1"></i>
                No Active Semester
            </span>
        @endif
    </div>

    <div class="row">
        @forelse($survey as $item)
            @php
                $alreadySubmitted = \App\Models\Response::where('survey_id', $item->id)
                    ->where('evaluator_id', auth()->id())
                    ->exists();
            @endphp

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 border-0 {{ $alreadySubmitted ? 'border-success' : '' }}">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-primary">{{ $item->title }}</h5>

                        <p class="card-text text-muted">
                            {{ $item->description ?? 'No description available.' }}
                        </p>

                        <ul class="list-unstyled small mb-3">
                            <li><strong>Evaluatee:</strong> {{ $item->evaluatee->name }}</li>
                            <li>
                                <strong>Status:</strong>
                                @if($item->is_active)
                                    <span class="text-success">Active</span>
                                @else
                                    <span class="text-danger">Inactive</span>
                                @endif
                            </li>
                            <li><strong>Course:</strong> {{ $item->group ?? 'N/A' }} - {{ $item->subject->course_code ?? 'N/A' }}</li>
                            <li><strong>Created:</strong> {{ $item->created_at->format('M d, Y') }}</li>
                        </ul>

                        @if($alreadySubmitted)
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success px-3 py-2">Submitted</span>
                                <button class="btn btn-secondary btn-sm" disabled>
                                    <i class="bi bi-lock-fill me-1"></i> Locked
                                </button>
                            </div>
                        @elseif($item->is_active)
                            <a href="{{ route('student.survey_take', $item->id) }}"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Evaluate
                            </a>
                        @else
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="bi bi-x-circle me-1"></i> Inactive
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    No surveys are currently available for you to take. Check back later!
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
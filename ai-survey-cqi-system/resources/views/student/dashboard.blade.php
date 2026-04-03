@extends('layouts.default')

@section('title', 'Student Dashboard')

@section('content')
<div class="container py-5">

    {{-- Header + Semester Badge --}}
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h3 class="mb-0">Welcome, {{ auth()->user()->name }}</h3>
            <p class="text-muted mb-0">Here's an overview of your subjects and survey activities.</p>
            <p class="text-muted mb-0"><strong>Role:</strong> {{ auth()->user()->roles->first()?->name ?? 'N/A' }}</p>
        </div>
        <div class="text-end">
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
    </div>

    <hr class="mb-4">

    {{-- Enrolled Subjects --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">My Enrolled Subjects</h5>
        </div>
        <div class="card-body">
            @if($subjects->isEmpty())
                <p class="text-muted">No subjects found. You might need to upload your study load.</p>
            @else
                <ul class="list-group">
                    @foreach($subjects as $subject)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $subject->course_code ?? 'N/A' }}</strong>
                                <span class="text-muted">{{ $subject->pivot->group ? ' - Group ' . $subject->pivot->group : '' }}</span>
                            </div>
                            <div>
                                @foreach($subject->teachers as $teacher)
                                    <span class="badge bg-secondary">{{ $teacher->name }}</span>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Active Surveys --}}
    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Active Surveys</h5>
            @if($activeSemester)
                <small class="opacity-75">{{ $activeSemester->name }}</small>
            @endif
        </div>
        <div class="card-body">
            @if($activeSurveys->isEmpty())
                <p class="text-muted">No active surveys available right now.</p>
            @else
                <ul class="list-group">
                    @foreach($activeSurveys as $survey)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $survey->title }}</strong>
                                <small class="text-muted d-block">{{ $survey->subject->course_code ?? '' }}</small>
                            </div>
                            @if($answeredSurveyIds->contains($survey->id))
                                <span class="badge bg-secondary">Completed</span>
                            @else
                                <a href="{{ route('student.survey_take', $survey->id) }}" class="btn btn-sm btn-outline-primary">Take Survey</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Recent Feedback --}}
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Recent Feedback</h5>
        </div>
        <div class="card-body">
            @if($recentResponses->isEmpty())
                <p class="text-muted">You haven't submitted any feedback yet.</p>
            @else
                <ul class="list-group">
                    @foreach($recentResponses as $response)
                        <li class="list-group-item">
                            <div>
                                <strong>{{ $response->survey->title ?? 'Unknown Survey' }}</strong><br>
                                <small class="text-muted">Subject: {{ $response->survey->subject->course_code ?? 'N/A' }}</small>
                            </div>
                            <div class="mt-1">
                                <small>Sentiment: <strong>{{ ucfirst($response->sentiment_label ?? 'n/a') }}</strong></small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

</div>
@endsection
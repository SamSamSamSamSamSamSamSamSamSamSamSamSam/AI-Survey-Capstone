@extends('layouts.default')

@section('title', 'Student Dashboard')

@section('content')
<div class="container py-5">
    <h3>Welcome, {{ auth()->user()->name }}</h3>
    <p class="text-muted mb-4">Here’s an overview of your subjects and survey activities.</p>
	<p class="text-muted mb-4"><strong>Role:</strong> {{ auth()->user()->roles->first()?->name ?? 'N/A' }}</p>


 
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
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Active Surveys</h5>
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
                                <a href="{{ route('student.survey', $survey->id) }}" class="btn btn-sm btn-outline-primary">Take Survey</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Recent Responses --}}
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Recent Feedback</h5>
        </div>
        <div class="card-body">
            @if($recentResponses->isEmpty())
                <p class="text-muted">You haven’t submitted any feedback yet.</p>
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

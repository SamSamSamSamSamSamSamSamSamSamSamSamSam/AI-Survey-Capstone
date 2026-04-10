@extends('layouts.default')

@section('header')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .teacher-welcome { margin-bottom: 1rem; }
        .card-compact { border-radius:.5rem; box-shadow:0 6px 18px rgba(0,0,0,0.03); }
    </style>
@endsection

@section('content')
<div class="container py-4">

    {{-- Header + Semester Badge --}}
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-0">Welcome, {{ auth()->user()->name }}</h3>
            <p class="text-muted mb-0"><strong>Role:</strong> {{ auth()->user()->roles->first()?->name ?? 'N/A' }}</p>
        </div>
        <div>
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

    {{-- My Classes --}}
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">My Classes</h5>
        </div>
        <div class="card-body">
            @forelse($classes as $class)
                <div class="mb-2">
                    <strong>{{ $class['code'] }} — {{ $class['title'] }}</strong>
                    <span class="text-muted ms-1">Group {{ $class['group'] }}</span>
                    <span class="badge bg-secondary ms-1">{{ $class['students'] }} students</span>
                </div>
            @empty
                <p class="text-muted mb-0">No classes assigned.</p>
            @endforelse
        </div>
    </div>

    {{-- Active Surveys --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Active Surveys</h5>
            @if($activeSemester)
                <small class="opacity-75">{{ $activeSemester->name }}</small>
            @endif
        </div>
        <div class="card-body">
            @if($activeSurveys->isEmpty())
                <p class="text-muted">No active surveys right now.</p>
            @else
                <ul class="list-group">
                    @foreach($activeSurveys as $survey)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $survey->title }}</strong>
                                <small class="text-muted d-block">{{ $survey->subject->course_code ?? '' }}</small>
                            </div>
                            <span class="badge bg-secondary">Active</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Top Performing Faculty --}}
    <div class="card card-compact p-3 mb-3">
        <h5>Top Performing Faculty</h5>
        <ul class="list-group list-group-flush">
            @forelse($topPerformers as $tp)
                <li class="list-group-item d-flex justify-content-between">
                    <div>{{ $tp['name'] }}</div>
                    <div class="text-muted">{{ $tp['avg_rating'] }} ({{ $tp['responses_count'] }} responses)</div>
                </li>
            @empty
                <li class="list-group-item text-muted">No top performers data available.</li>
            @endforelse
        </ul>
    </div>

</div>
@endsection
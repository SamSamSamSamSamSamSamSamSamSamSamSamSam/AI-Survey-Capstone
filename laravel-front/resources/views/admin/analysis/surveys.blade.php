@extends('layouts.default')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>Survey selection — Question Analysis</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="card p-3 mb-3">
        <h5>All surveys</h5>
        <p class="small text-muted">Click a survey to view question-level metrics and distributions. You can also view aggregated data for all surveys.</p>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{{ route('admin.analysis.questionAnalysis') }}">All surveys (aggregate)</a>
            </li>
            @foreach($surveys as $survey)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.analysis.questionAnalysis', ['survey_id' => $survey->id]) }}">
                            {{ $survey->title }}
                        </a>
                        <div class="small text-muted">Created: {{ $survey->created_at->toDateString() }}</div>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.analysis.questionAnalysis', ['survey_id' => $survey->id]) }}">
                        View analysis
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
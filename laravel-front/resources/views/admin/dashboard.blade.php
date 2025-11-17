@extends('layouts.default')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Faculty Improvement Dashboard</h2>
        
        <div class="d-flex align-items-center">
            <small class="text-muted me-3">Last updated: {{ now()->toDateTimeString() }}</small>
            <div class="admin-controls">
                <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-plus-circle me-1"></i> Create Survey
                </a>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-eye me-1"></i> View Surveys
                </a>
                <a href="{{ route('admin.reports.filter') }}" 
                    class="btn btn-sm btn-success ms-2">
                    <i class="bi bi-file-earmark-pdf"></i> Generate CQI Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <label for="survey-filter" class="form-label me-2 mb-0">
                <strong>Current View:</strong>
            </label>
            <select id="survey-filter"
                    class="form-select form-select-sm w-auto d-inline-block"
                    onchange="window.location.href = this.value;">
                <option value="{{ route('admin.dashboard') }}">
                    Overall (All Surveys)
                </option>
                @foreach($allSurveys as $survey)
                    <option value="{{ route('admin.dashboard', ['survey_id' => $survey->id]) }}"
                        {{ request('survey_id') == $survey->id ? 'selected' : '' }}>
                        {{ $survey->title }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- COURSE FILTER --}}
        <div class="ms-3">
            <label class="form-label me-2 mb-0"><strong>Course:</strong></label>
            <select id="course-filter" class="form-select form-select-sm w-auto d-inline-block"
                    onchange="window.location.href = this.value;">
                <option value="{{ route('admin.dashboard', ['survey_id' => request('survey_id')]) }}">
                    All Courses
                </option>

                @foreach($courses as $course)
                    <option value="{{ route('admin.dashboard', [
                        'survey_id' => request('survey_id'),
                        'course' => $course
                    ]) }}"
                        {{ request('course') == $course ? 'selected' : '' }}>
                        {{ $course }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <a href="{{ route('admin.analysis.surveys') }}" class="btn btn-sm btn-outline-primary me-2">
                <i class="bi bi-bar-chart"></i> Question Analysis
            </a>
            <a href="{{ route('admin.analysis.wordCloud') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-cloud"></i> Word Cloud
            </a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-start border-primary border-4">
                <h6>Total Responses</h6>
                <h3>{{ $distinct_evaluators ?? 0 }}</h3>
                <small class="text-muted">
                    Participation: <strong>{{ $participation_pct ?? 'N/A' }}%</strong> 
                    @if($eligible_evaluators) 
                        (of {{ $eligible_evaluators }} eligible)
                    @endif
                </small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-success border-4">
                <h6>Overall Mean Rating</h6>
                <h3 class="{{ $mean >= 4.0 ? 'text-success' : ($mean < 3.0 ? 'text-danger' : 'text-warning') }}">
                    {{ $mean !== null ? number_format($mean, 2) : 'N/A' }}
                </h3>
                <small class="text-muted">Target: 4.0. Higher is better.</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card p-3 border-start border-info border-4">
                <h6>Overall Positive Sentiment</h6>
                <h3>{{ $overallPositivePct ?? 'N/A' }}%</h3>
                <small class="text-muted">Percentage of positive qualitative comments.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-secondary border-4">
                <h6>Standard Deviation</h6>
                <h3 class="{{ $stddev < 0.8 ? 'text-success' : 'text-warning' }}">
                    {{ $stddev !== null ? number_format($stddev, 2) : 'N/A' }}
                </h3>
                <small class="text-muted">Lower = more consistent ratings.</small>
            </div>
        </div>
    </div>

    {{-- CATEGORY PERFORMANCE PANEL --}}
    <div class="card p-3 mb-4">
        <h5>Category Performance Summary</h5>
        <table class="table table-sm mt-2">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Average Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categoryScores as $cat)
                <tr>
                    <td>{{ $cat['category'] }}</td>
                    <td class="{{ $cat['avg'] >= 4.0 ? 'text-success fw-bold' : 'text-warning' }}">
                        {{ number_format($cat['avg'], 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2">No category rating data available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MONTHLY GRAPH + TOP PERFORMERS --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card p-3 mb-3">
                <h5>Monthly Performance Trend (Rating & Sentiment)</h5>
                <canvas id="monthlyCombinedChart" height="120"></canvas>
                <p class="mt-2 small text-muted">
                    Interpretation: Consistent upward trends indicate continuous improvement.
                </p>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h5>Top Performing Faculty <i class="bi bi-star-fill text-warning"></i></h5>
                <table class="table table-sm mt-2">
                    <thead><tr><th>Name</th><th>Avg</th><th>Positive %</th></tr></thead>
                    <tbody>
                        @forelse($topPerformers as $p)
                        <tr>
                            <td>
                                <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $p['evaluatee_id']]) }}" 
                                   class="text-decoration-none">
                                    {{ $p['name'] }}
                                </a>
                            </td>
                            <td class="{{ $p['avg_rating'] >= 4.5 ? 'fw-bold text-success' : '' }}">
                                {{ number_format($p['avg_rating'], 2) }}
                            </td>
                            <td class="{{ $p['positive_pct'] >= 80 ? 'text-primary' : '' }}">
                                {{ $p['positive_pct'] }}%
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">Not enough data (≥3 rating responses required).</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="small text-muted">Top 10 based on rating count & sentiment.</p>
            </div>
        </div>
    </div>
    
    {{-- FACULTY SENTIMENT BREAKDOWN --}}
    <div class="card p-3">
        <h5>Faculty Sentiment Breakdown</h5>
        <table class="table table-sm mt-2">
            <thead>
                <tr>
                    <th>Faculty</th>
                    <th>Total</th>
                    <th class="text-success">Positive %</th>
                    <th class="text-danger">Negative %</th>
                    <th class="text-warning">Neutral %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sentimentPerPerson as $s)
                <tr>
                    <td>
                        <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $s['evaluatee_id']]) }}">
                            {{ $s['name'] }}
                        </a>
                    </td>
                    <td>{{ $s['total'] }}</td>
                    <td class="text-success">{{ $s['positive_pct'] }}%</td>
                    <td class="text-danger">{{ $s['negative_pct'] }}%</td>
                    <td class="text-warning">{{ $s['neutral_pct'] }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No qualitative data available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <p class="small text-muted">Top 10 faculty with the most qualitative responses.</p>
    </div>

</div>

<style>
    #monthlyCombinedChart {
        width: 100% !important;
        height: 350px !important;
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    const dashboardData = {
        monthlyLabels: @json($monthlyLabels ?? []),
        monthlyAvg: @json($monthlyAvg ?? []),
        monthlyPosPct: @json($monthlyPositivePct ?? []),
    };
</script>
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush

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
                <a href="" 
                    class="btn btn-sm btn-success ms-2">
                    <i class="bi bi-file-earmark-pdf"></i> Generate CQI Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <label for="survey-filter" class="form-label me-2 mb-0"><strong>Current View:</strong></label>
            <select id="survey-filter" class="form-select form-select-sm w-auto d-inline-block" onchange="window.location.href = this.value;">
                <option value="{{ route('admin.dashboard') }}">Overall (All Surveys)</option>
                @foreach($allSurveys as $survey)
                    <option value="{{ route('admin.dashboard', ['survey_id' => $survey->id]) }}" {{ request('survey_id') == $survey->id ? 'selected' : '' }}>
                        {{ $survey->title }}
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-start border-primary border-4">
                <h6>Total Responses</h6>
                <h3>{{ $rating_count ?? 0 }}</h3>
                <small class="text-muted">
                    Participation: <strong>{{ $participation_pct ?? 'N/A' }}%</strong> 
                    @if($eligible_evaluators) (of {{ $eligible_evaluators }} eligible) @endif
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
                <h3>
                    {{-- This value is now calculated in the DashboardController --}}
                    {{ $overallPositivePct ?? 'N/A' }}%
                </h3>
                <small class="text-muted">Percentage of positive qualitative comments.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-secondary border-4">
                <h6>Standard Deviation</h6>
                <h3 class="{{ $stddev < 0.8 ? 'text-success' : 'text-warning' }}">
                    {{ $stddev !== null ? number_format($stddev, 2) : 'N/A' }}
                </h3>
                <small class="text-muted">Variation in ratings. <strong>Lower is better</strong> (more consistent).</small>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card p-3 mb-3">
                <h5>Monthly Performance Trend (Rating & Sentiment)</h5>
                <canvas id="monthlyCombinedChart" height="120"></canvas>
                <p class="mt-2 small text-muted">
                    Interpretation: An upward trend in both lines indicates sustained improvement. Disparity (e.g., high rating, low sentiment) warrants investigation into specific feedback.
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
                                <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $p['evaluatee_id']]) }}" class="text-decoration-none">
                                    {{ $p['name'] }}
                                </a>
                            </td>
                            <td class="{{ $p['avg_rating'] >= 4.5 ? 'fw-bold text-success' : '' }}">{{ number_format($p['avg_rating'], 2) }}</td>
                            <td class="{{ $p['positive_pct'] >= 80 ? 'text-primary' : '' }}">{{ $p['positive_pct'] }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">Not enough data (need ≥3 rating responses per instructor).</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="small text-muted">Filtered for instructors with at least 3 rating responses.</p>
            </div>
        </div>
    </div>
    
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
                {{-- This collection is now pre-sorted by total and sliced to the top 10 in the controller --}}
                @forelse($sentimentPerPerson as $s)
                <tr>
                    <td>
                        <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $s['evaluatee_id']]) }}">{{ $s['name'] }}</a>
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
        <p class="small text-muted">Top 10 faculty based on total qualitative responses.</p>
    </div>

</div>

{{-- This inline style is necessary to override potential conflicting styles and ensure chart responsiveness --}}
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
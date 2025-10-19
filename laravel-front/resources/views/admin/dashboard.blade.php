@extends('layouts.default')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Faculty Improvement Dashboard</h2>
        <small class="text-muted">Last updated: {{ now()->toDateTimeString() }}</small>
           <div class="admin-controls">
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle me-1"></i> Create Survey
            </a>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1 bi bi-eye"></i> View Surveys
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.analysis.surveys') }}" class="btn btn-sm btn-outline-primary me-2">
            <i class="bi bi-bar-chart"></i> Question Analysis
        </a>
        <a href="{{ route('admin.analysis.wordCloud') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-cloud"></i> Word Cloud
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card p-3">
                <h6>Mean</h6>
                <h3>{{ $mean !== null ? number_format($mean, 2) : 'N/A' }}</h3>
                <small class="text-muted">Average rating. Higher is better.</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h6>Median</h6>
                <h3>{{ $median !== null ? number_format($median, 2) : 'N/A' }}</h3>
                <small class="text-muted">Middle score; less affected by outliers.</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h6>Mode</h6>
                <h3>{{ $mode !== null ? $mode : 'N/A' }}</h3>
                <small class="text-muted">Most common rating given.</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h6>Std Dev</h6>
                <h3>{{ $stddev !== null ? number_format($stddev, 2) : 'N/A' }}</h3>
                <small class="text-muted">Variation in ratings.</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h6>Responses</h6>
                <h3>{{ $rating_count ?? 0 }}</h3>
                <small class="text-muted">Number of rating responses</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h6>Participation</h6>
                <h3>
                    {{ $distinct_evaluators ?? 0 }}
                    @if($eligible_evaluators)
                        <small class="d-block text-muted">{{ $participation_pct }}%</small>
                    @endif
                </h3>
                <small class="text-muted">Distinct evaluators (anonymous)</small>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card p-3 mb-3">
                <h5>Monthly Average Rating</h5>
                <canvas id="monthlyRatingChart" height="120"></canvas>
                <p class="mt-2 small text-muted">
                    Interpretation: upward trend indicates improvement. If avg < 3, investigate training.
                </p>
            </div>

            <div class="card p-3">
                <h5>Monthly Positive Sentiment %</h5>
                <canvas id="monthlySentimentChart" height="120"></canvas>
                <p class="mt-2 small text-muted">Shows % of positive qualitative feedback over time.</p>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h5>Top Performing Faculty</h5>
                <table class="table table-sm mt-2">
                    <thead><tr><th>Name</th><th>Avg</th><th>Responses</th></tr></thead>
                    <tbody>
                        @forelse($topPerformers as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $p['evaluatee_id']]) }}">
                                        {{ $p['name'] }}
                                    </a>
                                </td>
                                <td>{{ number_format($p['avg_rating'], 2) }}</td>
                                <td>{{ $p['count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Not enough data to determine top performers.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="small text-muted">Top performers include only instructors with ≥3 rating responses.</p>
            </div>

            <div class="card p-3">
                <h5>Sentiment Distribution (Per Person)</h5>
                <table class="table table-sm mt-2">
                    <thead><tr><th>Person</th><th>Total</th><th>Pos %</th><th>Neg %</th><th>Neutral %</th></tr></thead>
                    <tbody>
                        @foreach($sentimentPerPerson as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $row['evaluatee_id']]) }}">
                                        {{ $row['name'] }}
                                    </a>
                                </td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['positive_pct'] }}%</td>
                                <td>{{ $row['negative_pct'] }}%</td>
                                <td>{{ $row['neutral_pct'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthlyLabels = @json($monthlyLabels ?? []);
    const monthlyAvg = @json($monthlyAvg ?? []);
    const monthlyPosPct = @json($monthlyPositivePct ?? []);

    // Monthly rating line
    const ctx1 = document.getElementById('monthlyRatingChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Average Rating',
                data: monthlyAvg,
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.06)',
                tension: 0.2,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, suggestedMax: 5 } } }
    });

    // Monthly positive percent
    const ctx2 = document.getElementById('monthlySentimentChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Positive %',
                data: monthlyPosPct,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.06)',
                tension: 0.2,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, suggestedMax: 100 } } }
    });
</script>
@endsection
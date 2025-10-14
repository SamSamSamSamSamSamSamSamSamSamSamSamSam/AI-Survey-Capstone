@extends('layouts.default')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Faculty Improvement Dashboard</h2>
        <div class="admin-controls">
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle me-1"></i> Create Survey
            </a>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1 bi bi-eye"></i> View Surveys
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Mean Rating</h6>
                <h3>{{ $mean !== null ? number_format($mean, 2) : 'N/A' }}</h3>
                <small class="text-muted">Average score across rating questions</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Median Rating</h6>
                <h3>{{ $median !== null ? number_format($median, 2) : 'N/A' }}</h3>
                <small class="text-muted">Middle score (less sensitive to outliers)</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Mode Rating</h6>
                <h3>{{ $mode !== null ? $mode : 'N/A' }}</h3>
                <small class="text-muted">Most frequent rating</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Std. Deviation</h6>
                <h3>{{ $stddev !== null ? number_format($stddev, 2) : 'N/A' }}</h3>
                <small class="text-muted">Spread of ratings (lower = consistent)</small>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3">
                <h5>Monthly Average Rating</h5>
                <canvas id="monthlyChart" height="120"></canvas>
                <p class="mt-2 text-muted small">
                    Interpretation:
                    @if($monthlyAvg && count($monthlyAvg))
                        If the line trends upward, departmental ratings are improving; downward = decline; flat = stable.
                    @else
                        Not enough rating data to show a trend yet.
                    @endif
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3">
                <h5>Top Performing Faculty</h5>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr><th>Name</th><th>Avg</th><th>Responses</th></tr>
                    </thead>
                    <tbody>
                        @forelse($topPerformers as $p)
                            <tr>
                                <td>{{ $p['name'] }}</td>
                                <td>{{ number_format($p['avg_rating'], 2) }}</td>
                                <td>{{ $p['count'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No top performers (need at least 3 rating responses)</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="small text-muted">Top performers are sorted by average rating (min 3 responses).</p>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <h5>Sentiment Distribution by Person</h5>
        <table class="table table-striped mt-2">
            <thead><tr><th>Person</th><th>Total</th><th>Positive %</th><th>Negative %</th><th>Neutral %</th></tr></thead>
            <tbody>
                @foreach($sentimentPerPerson as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
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

<!-- Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthlyLabels = @json($monthlyLabels ?? []);
    const monthlyAvg = @json($monthlyAvg ?? []);

    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Average Rating',
                data: monthlyAvg,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.05)',
                tension: 0.2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, suggestedMax: 5 }
            }
        }
    });
</script>
@endsection

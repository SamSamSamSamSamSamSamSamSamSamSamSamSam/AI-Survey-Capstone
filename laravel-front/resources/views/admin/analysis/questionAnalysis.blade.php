@extends('layouts.default')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h3>Question-level Analysis
                @if(!empty($survey))
                    <small class="text-muted"> — Survey: {{ $survey->title }}</small>
                @endif
            </h3>
            @if($qWord)
                <div class="small text-muted">Showing all questions in the survey. Questions that contain "<strong>{{ $qWord }}</strong>" are highlighted.</div>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.analysis.surveys') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </div>

    @foreach($stats as $i => $s)
        <div class="card mb-3 p-3 {{ $s['matched'] ? 'border-primary' : '' }}">
            <h5>
                @if($s['matched'])
                    <span class="badge bg-primary me-2">Match</span>
                @endif
                {{ $s['question']->question_text }}
                <small class="text-muted">({{ ucfirst($s['type']) }})</small>
            </h5>

            @if($s['type'] === 'rating')
                <p class="small text-muted">Count: {{ $s['count'] }} — Mean: {{ $s['mean'] ?? 'N/A' }} — Median: {{ $s['median'] ?? 'N/A' }} — StdDev: {{ $s['stddev'] ?? 'N/A' }}</p>
                <canvas id="qChart{{ $i }}" height="80"></canvas>
                <script>
                    (function(){
                        const labels = [1,2,3,4,5];
                        const data = @json(array_values($s['distribution']));
                        const ctx = document.getElementById('qChart{{ $i }}').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{ label: 'Responses', data: data, backgroundColor: '#0d6efd' }]
                            },
                            options: { responsive: true, scales: { y: { beginAtZero: true } } }
                        });
                    })();
                </script>
            @else
                <p class="small text-muted">Text responses: {{ $s['count'] }} — Top words:</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($s['top_words'] as $word => $cnt)
                        <a href="{{ route('admin.analysis.questionAnalysis', ['survey_id' => $surveyId ?? null, 'q' => $word]) }}" class="badge bg-light text-dark border text-decoration-none">
                            {{ $word }} <small class="text-muted">({{ $cnt }})</small>
                        </a>
                    @endforeach
                </div>

                <div class="accordion" id="responsesAccordion{{ $i }}">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $i }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}" aria-expanded="false" aria-controls="collapse{{ $i }}">
                                View {{ $s['count'] }} responses
                            </button>
                        </h2>
                        <div id="collapse{{ $i }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $i }}" data-bs-parent="#responsesAccordion{{ $i }}">
                            <div class="accordion-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>When</th>
                                            <th>Response</th>
                                            <th>Sentiment</th>
                                            <th>Evaluator</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($s['responses'] as $r)
                                            <tr>
                                                <td style="white-space:nowrap;">{{ $r['created_at'] }}</td>
                                                <td style="white-space:pre-wrap;">{{ $r['response'] }}</td>
                                                <td>
                                                    {{ $r['sentiment_label'] ?? 'N/A' }}
                                                    @if($r['sentiment_score'] !== null) ({{ number_format($r['sentiment_score'], 3) }}) @endif
                                                </td>
                                                <td>{{ $r['evaluator'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            @endif
        </div>
    @endforeach
</div>
@endsection
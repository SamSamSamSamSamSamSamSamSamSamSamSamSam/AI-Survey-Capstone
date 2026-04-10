@extends('layouts.default')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>Evaluatee Details (Name: {{ $evaluatee->name }})</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <div class="card mb-3 p-3">
        <h5>Summary</h5>
        <p class="small text-muted">Ratings count: {{ $metrics['count'] }} — Mean: {{ $metrics['mean'] ?? 'N/A' }} — Median: {{ $metrics['median'] ?? 'N/A' }}</p>
    </div>

    <div class="card p-3">
        <h5>Responses (most recent first)</h5>
        <table class="table table-sm">
            <thead><tr><th>When</th><th>Question</th><th>Response</th><th>Sentiment</th><th>Evaluator</th></tr></thead>
            <tbody>
                @foreach($responses as $r)
                    <tr>
                        <td>{{ $r['created_at'] }}</td>
                        <td>{{ $r['question'] }}</td>
                        <td style="white-space:pre-wrap">{{ $r['response'] }}</td>
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
@endsection
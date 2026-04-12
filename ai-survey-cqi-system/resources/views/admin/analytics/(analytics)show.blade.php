@extends('admin.layouts.app')
@section('title', 'Analytics — ' . $analytic->survey->offering->subject->course_code)

@section('content')
<div class="page-header">
    <h1>Analytics Detail</h1>
    <div class="actions">
        @if ($existingReport)
            <a href="{{ route('admin.cqi-reports.show', $existingReport->id) }}" class="btn btn-secondary">View CQI Report</a>
        @endif
        <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

{{-- Header info --}}
<div class="alert alert-info" style="margin-bottom:1.25rem;font-size:.875rem;">
    <strong>{{ $analytic->survey->offering->subject->course_code }} — {{ $analytic->survey->offering->subject->name }}</strong>
    &nbsp;·&nbsp; {{ $analytic->survey->offering->teacher->name }}
    &nbsp;·&nbsp; {{ $analytic->survey->offering->semester->full_label }}
    &nbsp;·&nbsp; {{ $analytic->response_count }} respondent(s)
    &nbsp;·&nbsp; Last computed: {{ $analytic->last_computed_at?->format('M d, Y h:i A') }}
</div>

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#4f46e5;">{{ number_format($analytic->avg_rating ?? 0, 2) }}</div>
            <div style="font-size:.78rem;color:#6b7280;">Overall Avg Rating</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#065f46;">{{ number_format($analytic->positive_sentiment_percent ?? 0, 1) }}%</div>
            <div style="font-size:.78rem;color:#6b7280;">Positive Sentiment</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#92400e;">{{ number_format($analytic->neutral_sentiment_percent ?? 0, 1) }}%</div>
            <div style="font-size:.78rem;color:#6b7280;">Neutral Sentiment</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#b91c1c;">{{ number_format($analytic->negative_sentiment_percent ?? 0, 1) }}%</div>
            <div style="font-size:.78rem;color:#6b7280;">Negative Sentiment</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#374151;">{{ $analytic->response_count }}</div>
            <div style="font-size:.78rem;color:#6b7280;">Total Responses</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">

    {{-- Category scores --}}
    <div class="card">
        <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">Category Scores</div>
        @if ($analytic->category_scores)
            <table>
                <thead><tr><th>Category</th><th>Avg Score</th><th>Interpretation</th></tr></thead>
                <tbody>
                    @foreach ($analytic->category_scores as $cat => $score)
                    @php
                        $interp = match(true) {
                            $score >= 4.5 => 'Excellent',
                            $score >= 4.0 => 'Very Good',
                            $score >= 3.5 => 'Good',
                            $score >= 3.0 => 'Fair',
                            default       => 'Needs Improvement',
                        };
                        $color = $score >= 3.5 ? '#065f46' : '#92400e';
                    @endphp
                    <tr>
                        <td style="font-size:.85rem;">{{ $cat }}</td>
                        <td style="font-weight:600;color:{{ $color }};">{{ number_format($score, 2) }}</td>
                        <td style="font-size:.8rem;color:{{ $color }};">{{ $interp }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty-state" style="padding:1.5rem;">No category data.</p>
        @endif
    </div>

    {{-- Top keywords --}}
    <div class="card">
        <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">Top Keywords from Open-ended Responses</div>
        <div style="padding:1rem;">
            @if ($analytic->top_keywords)
                <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                    @foreach ($analytic->top_keywords as $i => $keyword)
                    <span style="background:{{ $i < 5 ? '#e0e7ff' : '#f3f4f6' }};color:{{ $i < 5 ? '#3730a3' : '#374151' }};padding:.2rem .6rem;border-radius:999px;font-size:.78rem;font-weight:{{ $i < 5 ? '600' : '400' }};">
                        {{ $keyword }}
                    </span>
                    @endforeach
                </div>
            @else
                <p style="color:#9ca3af;font-size:.875rem;">No text responses yet.</p>
            @endif
        </div>
    </div>
</div>

{{-- Open-ended responses with sentiment --}}
@if ($textResponses->isNotEmpty())
<div class="card" style="margin-bottom:1.5rem;">
    <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">Open-ended Responses &amp; Sentiment</div>
    @foreach ($textResponses as $questionId => $responses)
    <div style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;">
        <p style="font-weight:600;font-size:.875rem;margin-bottom:.5rem;">{{ $responses->first()->question->question_text }}</p>
        @foreach ($responses as $resp)
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:.35rem 0;border-bottom:1px solid #f9fafb;">
            <span style="font-size:.85rem;color:#374151;flex:1;">{{ $resp->text_response }}</span>
            @if ($resp->sentiment)
                @php
                    $label = $resp->sentiment->sentimentType->label;
                    $bg = match($label) { 'positive' => '#d1fae5', 'negative' => '#fee2e2', default => '#f3f4f6' };
                    $tc = match($label) { 'positive' => '#065f46', 'negative' => '#b91c1c', default => '#374151' };
                @endphp
                <span style="background:{{ $bg }};color:{{ $tc }};padding:.15rem .5rem;border-radius:999px;font-size:.7rem;font-weight:600;margin-left:.75rem;white-space:nowrap;">
                    {{ ucfirst($label) }} ({{ number_format($resp->sentiment->sentiment_score * 100, 1) }}%)
                </span>
            @else
                <span style="color:#9ca3af;font-size:.72rem;margin-left:.75rem;">Pending</span>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif

{{-- Generate CQI Report --}}
<div class="card">
    <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">
        Generate CQI Report
    </div>
    <div class="card-body">
        @if ($existingReport)
            <div class="alert alert-info" style="margin-bottom:1rem;">
                A CQI report already exists for this survey.
                <a href="{{ route('admin.cqi-reports.show', $existingReport->id) }}" style="font-weight:600;">View it →</a>
                Generating again will create a new version.
            </div>
        @endif

        @if (! $analytic->survey->is_active)
            <form method="POST" action="{{ route('admin.cqi-reports.generate') }}">
                @csrf
                <input type="hidden" name="survey_id" value="{{ $analytic->survey_id }}">
                <div class="form-group" style="max-width:300px;">
                    <label class="form-label">Report Scope</label>
                    <select name="scope_type" class="form-control">
                        <option value="survey">Survey (this survey only)</option>
                        <option value="offering">Offering (all surveys in this offering)</option>
                        <option value="faculty">Faculty (all surveys for this faculty)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Generate CQI report using Gemini AI? This may take a minute.')">
                    🤖 Generate CQI Report
                </button>
            </form>
        @else
            <div class="alert" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;">
                The survey must be <strong>deactivated</strong> before generating a CQI report.
            </div>
        @endif
    </div>
</div>
@endsection

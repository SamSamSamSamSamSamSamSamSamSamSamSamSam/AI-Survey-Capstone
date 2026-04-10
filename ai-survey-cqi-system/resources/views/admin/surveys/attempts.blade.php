@extends('admin.layouts.app')
@section('title', 'Responses — ' . $survey->title)

@section('content')
<div class="page-header">
    <h1>Responses</h1>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">← Back to Survey</a>
</div>

{{-- Survey meta --}}
<div class="alert alert-info" style="font-size:.875rem;margin-bottom:1.25rem;">
    <strong>{{ $survey->title }}</strong> &nbsp;·&nbsp;
    {{ $survey->offering->subject->course_code }} — {{ $survey->offering->subject->name }} &nbsp;·&nbsp;
    {{ $survey->offering->semester->full_label }} &nbsp;·&nbsp;
    Target: {{ ucfirst($survey->targetRole->name) }}
    <span style="float:right;font-weight:600;">{{ $attempts->total() }} response(s)</span>
</div>

@if ($attempts->isEmpty())
    <div class="card">
        <p class="empty-state">No submitted responses yet.</p>
    </div>
@else
    @foreach ($attempts as $attempt)
    <div class="card" style="margin-bottom:1rem;">

        {{-- Attempt header --}}
        <div style="padding:.7rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <span style="font-weight:600;font-size:.9rem;">{{ $attempt->respondent->name }}</span>
                <span style="color:#6b7280;font-size:.8rem;margin-left:.5rem;">{{ $attempt->respondent->user_id_number }}</span>
            </div>
            <span style="font-size:.78rem;color:#6b7280;">
                Submitted {{ $attempt->submitted_at->format('M d, Y h:i A') }}
            </span>
        </div>

        {{-- Responses --}}
        <div style="padding:1rem;">
            @foreach ($attempt->responses->sortBy('question.order') as $response)
            <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #f3f4f6;">
                <div style="font-size:.78rem;color:#6b7280;margin-bottom:.25rem;">
                    Q{{ $response->question->order }}
                    @if ($response->question->category)
                        &nbsp;·&nbsp; <span style="background:#f3f4f6;padding:.1rem .4rem;border-radius:4px;">{{ $response->question->category }}</span>
                    @endif
                </div>
                <div style="font-size:.875rem;color:#374151;margin-bottom:.4rem;">
                    {{ $response->question->question_text }}
                </div>

                @if ($response->question->isRating())
                    {{-- Star-like rating display --}}
                    <div style="display:flex;gap:.3rem;align-items:center;">
                        @for ($i = 1; $i <= 5; $i++)
                            <span style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;
                                {{ $i <= $response->rating_value
                                    ? 'background:#4f46e5;color:#fff;'
                                    : 'background:#e5e7eb;color:#9ca3af;' }}">
                                {{ $i }}
                            </span>
                        @endfor
                        <span style="font-size:.8rem;color:#6b7280;margin-left:.5rem;">
                            {{ match((int)$response->rating_value) {
                                1 => 'Strongly Disagree',
                                2 => 'Disagree',
                                3 => 'Neutral',
                                4 => 'Agree',
                                5 => 'Strongly Agree',
                                default => ''
                            } }}
                        </span>
                    </div>
                @else
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:.6rem .85rem;font-size:.875rem;color:#374151;font-style:italic;">
                        {{ $response->text_response ?: '(no response)' }}
                    </div>
                    {{-- Sentiment badge if analysed --}}
                    @if ($response->sentiment)
                        @php
                            $label = $response->sentiment->sentimentType->label;
                            $badgeStyle = match($label) {
                                'positive' => 'background:#d1fae5;color:#065f46;',
                                'negative' => 'background:#fee2e2;color:#b91c1c;',
                                default    => 'background:#f3f4f6;color:#374151;',
                            };
                        @endphp
                        <span style="display:inline-block;margin-top:.4rem;padding:.15rem .55rem;border-radius:999px;font-size:.7rem;font-weight:600;{{ $badgeStyle }}">
                            {{ ucfirst($label) }} ({{ number_format($response->sentiment->sentiment_score * 100, 1) }}%)
                        </span>
                    @endif
                @endif
            </div>
            @endforeach
        </div>

    </div>
    @endforeach

    <div class="pagination">{{ $attempts->links('pagination::simple-tailwind') }}</div>
@endif
@endsection

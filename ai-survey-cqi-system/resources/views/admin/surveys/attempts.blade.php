@extends('layouts.app')
@section('title', 'Responses — ' . $survey->title)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.show', $survey->id) }}">{{ Str::limit($survey->title, 30) }}</a></li>
    <li class="breadcrumb-item active">Responses</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Survey Responses</h2>
        <p class="page-subheading">
            {{ $survey->title }} ·
            {{ $survey->offering->subject->course_code }} ·
            {{ $survey->offering->semester->full_label }}
        </p>
    </div>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Survey
    </a>
</div>

{{-- ===== META STRIP ===== --}}
<div class="attempts-meta-strip mb-4">
    <div class="attempts-meta-strip__item">
        <i class="bi bi-people me-1"></i>
        Target: <strong>{{ ucfirst($survey->targetRole->name) }}</strong>
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-person-workspace me-1"></i>
        {{ $survey->offering->teacher->name }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item attempts-meta-strip__item--count">
        <i class="bi bi-chat-left-text me-1"></i>
        {{ $attempts->total() }} response(s)
    </div>
</div>

@if ($attempts->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-chat-left-dots"></i></div>
            <p class="empty-state-text">No submitted responses yet.</p>
        </div>
    </div>
@else

    @foreach ($attempts as $attempt)
    <div class="attempt-card mb-3">

        {{-- Attempt header --}}
        <div class="attempt-card__header">
            <div class="d-flex align-items-center gap-3">
                <div class="user-avatar-sm">
                    {{ strtoupper(substr($attempt->respondent->name, 0, 2)) }}
                </div>
                <div>
                    <div class="fw-500" style="font-size: .9rem;">
                        {{ $attempt->respondent->name }}
                    </div>
                    <div class="text-muted-sm text-mono">
                        {{ $attempt->respondent->user_id_number }}
                    </div>
                </div>
            </div>
            <div class="attempt-card__timestamp">
                <i class="bi bi-clock me-1"></i>
                {{ $attempt->submitted_at->format('M d, Y h:i A') }}
            </div>
        </div>

        {{-- Responses --}}
        <div class="attempt-card__body">
            @foreach ($attempt->responses->sortBy('question.order') as $response)
            <div class="response-row">

                {{-- Question meta --}}
                <div class="response-row__meta">
                    <span class="response-row__num">Q{{ $response->question->order }}</span>
                    @if ($response->question->category)
                        <span class="category-tag">{{ $response->question->category }}</span>
                    @endif
                </div>

                {{-- Question text --}}
                <p class="response-row__question">{{ $response->question->question_text }}</p>

                {{-- Answer --}}
                @if ($response->question->isRating())
                    <div class="rating-display">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="rating-dot {{ $i <= $response->rating_value ? 'rating-dot--filled' : '' }}">
                                {{ $i }}
                            </div>
                        @endfor
                        <span class="rating-label">
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
                    <div class="open-response">
                        {{ $response->text_response ?: '(no response)' }}
                    </div>
                    @if ($response->sentiment)
                        @php
                            $label = $response->sentiment->sentimentType->label;
                            $type  = match($label) {
                                'positive' => 'positive',
                                'negative' => 'negative',
                                default    => 'neutral',
                            };
                        @endphp
                        <span class="sentiment-badge sentiment-badge--{{ $type }}">
                            {{ ucfirst($label) }}
                            ({{ number_format($response->sentiment->sentiment_score * 100, 1) }}%)
                        </span>
                    @endif
                @endif

            </div>
            @endforeach
        </div>

    </div>
    @endforeach

    @if ($attempts->hasPages())
        <div class="table-pagination mt-2">
            {{ $attempts->links() }}
        </div>
    @endif

@endif

@endsection
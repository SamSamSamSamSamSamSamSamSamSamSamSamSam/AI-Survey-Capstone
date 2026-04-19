@extends('layouts.app')
@section('title', 'Analytics — ' . $analytic->survey->offering->subject->course_code)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Analytics</a></li>
    <li class="breadcrumb-item active">
        {{ $analytic->survey->offering->subject->course_code }}
    </li>
</ol>
@endsection

@section('content')

{{-- ===== PAGE HEADER ===== --}}
<div class="page-header flex-wrap gap-2">
    <div>
        <h2 class="page-heading">Analytics Detail</h2>
        <p class="page-subheading">
            {{ $analytic->survey->offering->subject->course_code }} —
            {{ $analytic->survey->offering->subject->name }} ·
            {{ $analytic->survey->offering->teacher->name }} ·
            {{ $analytic->survey->offering->semester->full_label }}
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($existingReport)
            <a href="{{ route('admin.cqi-reports.show', $existingReport->id) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-clipboard-data me-1"></i> View CQI Report
            </a>
        @endif
        <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== META STRIP ===== --}}
<div class="attempts-meta-strip mb-4">
    <div class="attempts-meta-strip__item">
        <i class="bi bi-person-workspace me-1"></i>
        <strong>{{ $analytic->survey->offering->teacher->name }}</strong>
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-calendar3 me-1"></i>
        {{ $analytic->survey->offering->semester->full_label }}
    </div>
    <div class="attempts-meta-strip__sep"></div>
    <div class="attempts-meta-strip__item">
        <i class="bi bi-clock me-1"></i>
        Last computed: {{ $analytic->last_computed_at?->format('M d, Y h:i A') ?? 'Never' }}
    </div>
    <div class="attempts-meta-strip__item attempts-meta-strip__item--count">
        <i class="bi bi-people me-1"></i>
        {{ $analytic->response_count }} respondent(s)
    </div>
</div>

{{-- ===== KPI CARDS ===== --}}
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="card analytic-kpi-card h-100">
            <div class="analytic-kpi-card__accent analytic-kpi-card__accent--blue"></div>
            <div class="card-body">
                <div class="analytic-kpi-card__label">Overall Avg Rating</div>
                <div class="analytic-kpi-card__value analytic-kpi-card__value--blue">
                    {{ number_format($analytic->avg_rating ?? 0, 2) }}
                    <span class="analytic-kpi-card__scale">/ 5</span>
                </div>
                <div class="analytic-kpi-card__bar">
                    <div class="analytic-kpi-card__bar-fill analytic-kpi-card__bar-fill--blue"
                         style="width: {{ (($analytic->avg_rating ?? 0) / 5) * 100 }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card analytic-kpi-card h-100">
            <div class="analytic-kpi-card__accent analytic-kpi-card__accent--green"></div>
            <div class="card-body">
                <div class="analytic-kpi-card__label">Positive Sentiment</div>
                <div class="analytic-kpi-card__value analytic-kpi-card__value--green">
                    {{ number_format($analytic->positive_sentiment_percent ?? 0, 1) }}<span class="analytic-kpi-card__scale">%</span>
                </div>
                <div class="analytic-kpi-card__bar">
                    <div class="analytic-kpi-card__bar-fill analytic-kpi-card__bar-fill--green"
                         style="width: {{ $analytic->positive_sentiment_percent ?? 0 }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card analytic-kpi-card h-100">
            <div class="analytic-kpi-card__accent analytic-kpi-card__accent--amber"></div>
            <div class="card-body">
                <div class="analytic-kpi-card__label">Neutral Sentiment</div>
                <div class="analytic-kpi-card__value analytic-kpi-card__value--amber">
                    {{ number_format($analytic->neutral_sentiment_percent ?? 0, 1) }}<span class="analytic-kpi-card__scale">%</span>
                </div>
                <div class="analytic-kpi-card__bar">
                    <div class="analytic-kpi-card__bar-fill analytic-kpi-card__bar-fill--amber"
                         style="width: {{ $analytic->neutral_sentiment_percent ?? 0 }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card analytic-kpi-card h-100">
            <div class="analytic-kpi-card__accent analytic-kpi-card__accent--red"></div>
            <div class="card-body">
                <div class="analytic-kpi-card__label">Negative Sentiment</div>
                <div class="analytic-kpi-card__value analytic-kpi-card__value--red">
                    {{ number_format($analytic->negative_sentiment_percent ?? 0, 1) }}<span class="analytic-kpi-card__scale">%</span>
                </div>
                <div class="analytic-kpi-card__bar">
                    <div class="analytic-kpi-card__bar-fill analytic-kpi-card__bar-fill--red"
                         style="width: {{ $analytic->negative_sentiment_percent ?? 0 }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== CATEGORY + KEYWORDS GRID ===== --}}
<div class="row g-3 mb-4">

    {{-- Category scores --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Category Scores</h5>
                @if ($analytic->category_scores)
                    <div class="category-score-list">
                        @foreach ($analytic->category_scores as $cat => $score)
                            @php
                                // Skip the stats object to avoid the calculation error
                                if ($cat === '_overall_stats') continue;

                                $score = (float) $score; // Ensure it's treated as a number
                                $pct = ($score / 5) * 100;
                                
                                $interp = match(true) {
                                    $score >= 4.5 => ['label' => 'Excellent',          'cls' => 'high'],
                                    $score >= 4.0 => ['label' => 'Very Good',          'cls' => 'high'],
                                    $score >= 3.5 => ['label' => 'Good',               'cls' => 'mid'],
                                    $score >= 3.0 => ['label' => 'Fair',               'cls' => 'mid'],
                                    default       => ['label' => 'Needs Improvement',  'cls' => 'low'],
                                };
                        @endphp
                        <div class="category-score-row">
                            <div class="category-score-row__header">
                                <span class="category-score-row__name">{{ $cat }}</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="category-score-row__interp category-score-row__interp--{{ $interp['cls'] }}">
                                        {{ $interp['label'] }}
                                    </span>
                                    <span class="category-score-row__val">{{ number_format($score, 2) }}</span>
                                </div>
                            </div>
                            <div class="category-score-row__track">
                                <div class="category-score-row__fill category-score-row__fill--{{ $interp['cls'] }}"
                                     style="width: 0%"
                                     data-width="{{ $pct }}%">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 32px 0;">
                        <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
                        <p class="empty-state-text">No category data available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Keywords --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Top Keywords</h5>
                <p class="text-muted-sm mb-3">From open-ended responses</p>
                @if ($analytic->top_keywords)
                    <div class="keyword-cloud">
                        @foreach ($analytic->top_keywords as $i => $keyword)
                            <span class="keyword-tag keyword-tag--{{ $i < 3 ? 'primary' : ($i < 7 ? 'secondary' : 'tertiary') }}">
                                {{ $keyword }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 32px 0;">
                        <div class="empty-state-icon"><i class="bi bi-chat-square-text"></i></div>
                        <p class="empty-state-text">No text responses yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ===== OPEN-ENDED RESPONSES ===== --}}
@if ($textResponses->isNotEmpty())
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Open-ended Responses &amp; Sentiment</h5>

        <div class="accordion analytic-accordion" id="responseAccordion">
            @foreach ($textResponses as $questionId => $responses)
            @php $accId = 'q-' . $questionId; @endphp
            <div class="accordion-item analytic-accordion__item">
                <h2 class="accordion-header">
                    <button class="accordion-button analytic-accordion__btn {{ $loop->first ? '' : 'collapsed' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $accId }}">
                        <span class="analytic-accordion__q-text">
                            {{ $responses->first()->question->question_text }}
                        </span>
                        <span class="analytic-accordion__q-count ms-3">
                            {{ $responses->count() }} response(s)
                        </span>
                    </button>
                </h2>
                <div id="{{ $accId }}"
                     class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                     data-bs-parent="#responseAccordion">
                    <div class="accordion-body analytic-accordion__body">
                        @foreach ($responses as $resp)
                        <div class="text-response-row">
                            <div class="text-response-row__text">
                                {{ $resp->text_response ?: '(no response)' }}
                            </div>
                            @if ($resp->sentiment)
                                @php
                                    $label = $resp->sentiment->sentimentType->label;
                                    $type  = match($label) { 'positive' => 'positive', 'negative' => 'negative', default => 'neutral' };
                                @endphp
                                <span class="sentiment-badge sentiment-badge--{{ $type }} flex-shrink-0">
                                    {{ ucfirst($label) }}
                                    ({{ number_format($resp->sentiment->sentiment_score * 100, 1) }}%)
                                </span>
                            @else
                                <span class="sentiment-badge sentiment-badge--neutral flex-shrink-0">
                                    Pending
                                </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>
@endif

<!-- // ----- Generate CQI Report Section ----- -->
<div class="card">
    <div class="card-body">
        {{-- ... (Header and Existing Report logic stays the same) ... --}}

        @php
            $hasGeminiKey = !empty(setting('ai.gemini_api_key'));
        @endphp

        @if (!$hasGeminiKey)
            {{-- API KEY MISSING WARNING --}}
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">AI Configuration Missing</h6>
                    <p class="mb-0 small">
                        The Gemini API key is not configured. Please 
                        <a href="{{ route('admin.settings.index', ['tab' => 'ai']) }}" class="alert-link">visit Settings</a> 
                        to set up the API key before generating reports.
                    </p>
                </div>
            </div>
        @elseif (! $analytic->survey->is_active)
            <form method="POST" action="{{ route('admin.cqi-reports.generate') }}"
                  id="generateCqiForm"
                  data-confirm="Generate a CQI report using Gemini AI? This may take up to a minute.">
                @csrf
                <input type="hidden" name="survey_id" value="{{ $analytic->survey_id }}">

                <div class="mb-3" style="max-width: 320px;">
                    <label class="form-label">Report Scope</label>
                    <select name="scope_type" class="form-select">
                        <option value="survey">Survey — this survey only</option>
                        <option value="offering">Offering — all surveys in this offering</option>
                        <option value="faculty">Faculty — all surveys for this faculty</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" id="generateBtn">
                    <i class="bi bi-robot me-2"></i> Generate CQI Report
                </button>
            </form>
        @else
            {{-- Survey still active notice --}}
            <div class="info-notice">
                <i class="bi bi-lock-fill info-notice__icon"></i>
                <div>
                    The survey must be <strong>deactivated</strong> before generating a CQI report.
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ---- Animate category bar fills on page load ----
    document.querySelectorAll('.category-score-row__fill').forEach(function (el) {
        const target = el.dataset.width;
        requestAnimationFrame(function () {
            setTimeout(function () {
                el.style.width = target;
            }, 120);
        });
    });

    // ---- Generate CQI: show AI loading state on submit ----
    const genForm = document.getElementById('generateCqiForm');
    const genBtn  = document.getElementById('generateBtn');
    if (genForm && genBtn) {
        genForm.addEventListener('submit', function () {
            genBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating with AI…';
            genBtn.disabled = true;
        });
    }
})();
</script>
@endpush
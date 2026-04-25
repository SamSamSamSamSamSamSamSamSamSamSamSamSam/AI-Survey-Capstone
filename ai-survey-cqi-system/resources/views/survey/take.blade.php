<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }} — CQI System</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* ============================================================
           SURVEY TAKE — Google Forms-inspired, clean & focused
           ============================================================ */

        body.survey-take-page {
            background: #f0f4f9;
            min-height: 100vh;
            padding-bottom: 100px; /* room for sticky bar */
        }

        /* ── Topbar ── */
        .st-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 0 1.25rem;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .st-topbar__back {
            color: #5f6368;
            text-decoration: none;
            font-size: 1.1rem;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background .15s;
        }
        .st-topbar__back:hover { background: #f1f3f4; color: #202124; }

        .st-topbar__title {
            font-size: .875rem;
            font-weight: 500;
            color: #202124;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .st-topbar__progress {
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-shrink: 0;
        }

        .st-progress-track {
            width: 140px;
            height: 4px;
            background: #e8eaed;
            border-radius: 999px;
            overflow: hidden;
        }

        .st-progress-fill {
            height: 100%;
            background: var(--bs-primary, #1a73e8);
            border-radius: 999px;
            transition: width .3s ease;
        }

        .st-progress-label {
            font-size: .75rem;
            color: #5f6368;
            white-space: nowrap;
        }

        /* ── Layout ── */
        .st-content {
            max-width: 680px;
            margin: 0 auto;
            padding: 1.5rem 1rem 2rem;
        }

        /* ── Survey header card ── */
        .st-header-card {
            background: #fff;
            border-radius: 8px;
            border-top: 8px solid var(--bs-primary, #1a73e8);
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.06), 0 1px 6px rgba(0,0,0,.04);
        }

        .st-header-card__title {
            font-size: 1.5rem;
            font-weight: 400;
            color: #202124;
            margin-bottom: .5rem;
            line-height: 1.3;
        }

        .st-header-card__desc {
            font-size: .9rem;
            color: #5f6368;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .st-meta-tag {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .75rem;
            color: #5f6368;
            background: #f1f3f4;
            border-radius: 4px;
            padding: .2rem .55rem;
        }

        .st-required-note {
            font-size: .78rem;
            color: #d93025;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        /* ── Category section ── */
        .st-section {
            margin-bottom: 1rem;
        }

        .st-section-header {
            background: #fff;
            border-radius: 8px;
            border-left: 4px solid var(--bs-primary, #1a73e8);
            padding: .9rem 1.25rem;
            margin-bottom: .5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
        }

        .st-section-header__name {
            font-size: 1rem;
            font-weight: 500;
            color: #202124;
            margin: 0 0 .15rem;
        }

        .st-section-header__desc {
            font-size: .8rem;
            color: #5f6368;
            margin: 0;
        }

        /* ── Question card ── */
        .st-question {
            background: #fff;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            margin-bottom: .5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.06), 0 1px 6px rgba(0,0,0,.04);
            border-left: 3px solid transparent;
            transition: border-color .2s, box-shadow .2s;
            position: relative;
        }

        .st-question:focus-within {
            border-left-color: var(--bs-primary, #1a73e8);
            box-shadow: 0 1px 3px rgba(0,0,0,.1), 0 2px 8px rgba(0,0,0,.08);
        }

        .st-question--answered {
            border-left-color: #34a853;
        }

        .st-question--error {
            border-left-color: #d93025;
        }

        .st-question__meta {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: .65rem;
            flex-wrap: wrap;
        }

        .st-question__num {
            font-size: .72rem;
            font-weight: 600;
            color: #9aa0a6;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .st-question__type {
            font-size: .68rem;
            font-weight: 600;
            border-radius: 3px;
            padding: .1rem .4rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .st-question__type--rating {
            background: #e8f0fe;
            color: #1a73e8;
        }

        .st-question__type--open {
            background: #e6f4ea;
            color: #188038;
        }

        .st-question__required {
            font-size: .68rem;
            color: #d93025;
            font-weight: 600;
        }

        .st-question__text {
            font-size: .9375rem;
            color: #202124;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .st-question__required-star {
            color: #d93025;
            margin-left: .2rem;
        }

        /* ── Scale / Likert row ── */
        .st-scale {
            width: 100%;
        }

        /* Labels row (only shown when there are labels) */
        .st-scale__labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: .4rem;
        }

        .st-scale__label-text {
            font-size: .72rem;
            color: #5f6368;
            text-align: center;
            flex: 1;
        }

        .st-scale__label-text:first-child { text-align: left; }
        .st-scale__label-text:last-child  { text-align: right; }

        /* Options row */
        .st-scale__options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .25rem;
        }

        .st-scale-opt {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .3rem;
            cursor: pointer;
        }

        .st-scale-opt__value {
            font-size: .8rem;
            font-weight: 500;
            color: #5f6368;
            line-height: 1;
        }

        .st-scale-opt__radio {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #9aa0a6;
            border-radius: 50%;
            cursor: pointer;
            transition: border-color .15s, background .15s, transform .15s;
            position: relative;
            flex-shrink: 0;
        }

        .st-scale-opt__radio:hover {
            border-color: var(--bs-primary, #1a73e8);
            transform: scale(1.1);
        }

        .st-scale-opt__radio:checked {
            border-color: var(--bs-primary, #1a73e8);
            background: var(--bs-primary, #1a73e8);
        }

        .st-scale-opt__radio:checked::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .st-scale-opt__label {
            font-size: .68rem;
            color: #5f6368;
            text-align: center;
            line-height: 1.2;
            max-width: 60px;
        }

        /* Selected state — highlight entire column */
        .st-scale-opt:has(.st-scale-opt__radio:checked) .st-scale-opt__value,
        .st-scale-opt:has(.st-scale-opt__radio:checked) .st-scale-opt__label {
            color: var(--bs-primary, #1a73e8);
            font-weight: 600;
        }

        /* ── Text answer ── */
        .st-text-answer {
            width: 100%;
            border: none;
            border-bottom: 1px solid #e0e0e0;
            border-radius: 0;
            padding: .4rem 0;
            font-size: .9rem;
            color: #202124;
            background: transparent;
            resize: none;
            outline: none;
            transition: border-color .2s;
            line-height: 1.5;
        }

        .st-text-answer:focus {
            border-bottom-color: var(--bs-primary, #1a73e8);
            border-bottom-width: 2px;
        }

        .st-text-answer::placeholder { color: #9aa0a6; }

        .st-char-count {
            font-size: .7rem;
            color: #9aa0a6;
            text-align: right;
            margin-top: .25rem;
        }

        /* ── Error message ── */
        .st-error {
            font-size: .78rem;
            color: #d93025;
            margin-top: .5rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* ── Bottom submit bar ── */
        .st-submit-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e0e0e0;
            padding: .75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0,0,0,.06);
        }

        .st-submit-bar__cancel {
            font-size: .825rem;
            color: #5f6368;
            text-decoration: none;
            padding: .4rem .75rem;
            border-radius: 4px;
            transition: background .15s;
            white-space: nowrap;
        }
        .st-submit-bar__cancel:hover { background: #f1f3f4; color: #202124; }

        .st-submit-bar__spacer { flex: 1; }

        .st-submit-bar__count {
            font-size: .78rem;
            color: #5f6368;
            white-space: nowrap;
        }

        .st-submit-bar__btn {
            border-radius: 4px;
            font-size: .875rem;
            font-weight: 500;
            padding: .45rem 1.25rem;
            white-space: nowrap;
        }

        /* ── Notification toggles inline ── */
        .st-notify {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }

        /* ── Toggle switch styles (nt-*) ── */
        .nt-label {
            font-size: .8rem;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            margin: 0;
            user-select: none;
            transition: color .2s;
        }

        .nt-switch {
            position: relative;
            display: inline-block;
            width: 34px;
            height: 18px;
            cursor: pointer;
            margin: 0;
            flex-shrink: 0;
        }

        .nt-switch__input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .nt-switch__track {
            position: absolute;
            inset: 0;
            background: #e5e7eb;
            border-radius: 999px;
            transition: background .2s;
        }

        .nt-switch__thumb {
            position: absolute;
            width: 12px;
            height: 12px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0,0,0,.2);
            transition: transform .2s;
        }

        .nt-switch__input:checked ~ .nt-switch__track {
            background: var(--bs-primary, #1a73e8);
        }

        .nt-switch__input:checked ~ .nt-switch__thumb {
            transform: translateX(16px);
        }

        .nt-label.is-on {
            color: var(--bs-primary, #1a73e8);
            font-weight: 600;
        }

        /* ── Dark mode ── */
        [data-bs-theme="dark"] body.survey-take-page,
        [data-bs-theme="dark"] .survey-take-page {
            background: #1a1a2e;
        }

        [data-bs-theme="dark"] .st-topbar,
        [data-bs-theme="dark"] .st-header-card,
        [data-bs-theme="dark"] .st-section-header,
        [data-bs-theme="dark"] .st-question,
        [data-bs-theme="dark"] .st-submit-bar {
            background: #2d2d3a;
            border-color: #3a3a4a;
            color: #e8eaed;
        }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .st-progress-track { width: 80px; }
            .st-progress-label { display: none; }
            .st-submit-bar__count { display: none; }
            .st-notify { display: none; } /* toggles move to form bottom on mobile */
            .st-notify-mobile { display: flex !important; }
            .st-scale__options { gap: .1rem; }
            .st-scale-opt__label { display: none; }
        }
    </style>
</head>
<body class="survey-take-page">

{{-- ===== STICKY TOPBAR ===== --}}
<header class="st-topbar">
    <a href="{{ route('survey.index') }}" class="st-topbar__back" title="Back">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="st-topbar__title">{{ Str::limit($survey->title, 50) }}</div>
    <div class="st-topbar__progress">
        <div class="st-progress-track">
            <div class="st-progress-fill" id="progressFill" style="width:0%"></div>
        </div>
        <span class="st-progress-label" id="progressLabel">
            0 / {{ $survey->questions->count() }} answered
        </span>
    </div>
</header>

{{-- ===== MAIN CONTENT ===== --}}
<main class="st-content">

    {{-- Survey header card --}}
    <div class="st-header-card">
        <h1 class="st-header-card__title">{{ $survey->title }}</h1>
        @if ($survey->description)
            <p class="st-header-card__desc">{{ $survey->description }}</p>
        @endif
        <div class="d-flex flex-wrap gap-2">
            <span class="st-meta-tag">
                <i class="bi bi-book"></i>
                {{ $survey->offering->subject->course_code }} — {{ $survey->offering->subject->name }}
            </span>
            <span class="st-meta-tag">
                <i class="bi bi-person"></i>
                {{ $survey->offering->teacher->name }}
            </span>
            <span class="st-meta-tag">
                <i class="bi bi-calendar3"></i>
                {{ $survey->offering->semester->full_label }}
            </span>
            <span class="st-meta-tag">
                <i class="bi bi-list-check"></i>
                {{ $survey->questions->count() }} question(s)
            </span>
        </div>
        <p class="st-required-note">
            <span style="color:#d93025;">*</span> Required questions must be answered before submitting.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-radius:8px;font-size:.875rem;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Please answer all required questions before submitting.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('survey.submit', $survey->id) }}" id="surveyForm">
        @csrf

        @php
            // Group questions by category_id; null → 0 (General)
            // Preserve the actual category model on each group via the first question
            $grouped = $survey->questions->groupBy(function($q) {
                return (int) ($q->category_id ?? 0);
            });

            // Sort: General (key=0) first, then the rest
            $grouped = $grouped->sortKeys();

            $questionCounter = 0;
        @endphp

        @foreach ($grouped as $categoryId => $questions)
        @php
            // category_id=0 means uncategorized → show as "General"
            $category = ($categoryId !== 0)
                ? $questions->first()->category   // already eager-loaded via controller
                : null;
        @endphp

        <div class="st-section">

            {{-- Section header --}}
            <div class="st-section-header">
                <h2 class="st-section-header__name">
                    {{ $category ? $category->name : 'General' }}
                </h2>
                @if ($category && $category->description)
                    <p class="st-section-header__desc">{{ $category->description }}</p>
                @endif
            </div>

            {{-- Questions in this category --}}
            @foreach ($questions as $question)
            @php
                $questionCounter++;
                $hasError = $errors->has("responses.{$question->id}");
            @endphp

            <div class="st-question {{ $hasError ? 'st-question--error' : '' }}"
                 id="qcard-{{ $question->id }}">

                {{-- Meta row --}}
                <div class="st-question__meta">
                    <span class="st-question__num">Q{{ $questionCounter }}</span>
                    {{-- @if ($question->isRating())
                        <span class="st-question__type st-question__type--rating">Scale</span>
                    @else
                        <span class="st-question__type st-question__type--open">Open-ended</span>
                    @endif --}}
                </div>

                {{-- Question text --}}
                <p class="st-question__text">
                    {{ $question->question_text }}
                    @if ($question->isRating())
                        <span class="st-question__required-star">*</span>
                    @endif
                </p>

                {{-- ── RATING: Likert scale row ── --}}
                @if ($question->isRating())
                    @php
                        $scaleOptions = $question->scale?->options->sortBy('order_number') ?? collect();
                        $hasLabels    = $scaleOptions->contains(fn($o) => filled($o->label));
                    @endphp

                    <div class="st-scale" role="radiogroup" aria-label="{{ $question->question_text }}">

                        {{-- Label row (lowest / highest endpoint labels) --}}
                        @if ($hasLabels && $scaleOptions->count() > 1)
                        <div class="st-scale__labels">
                            <span class="st-scale__label-text">{{ $scaleOptions->first()->label }}</span>
                            <span class="st-scale__label-text"></span>{{-- spacer --}}
                            <span class="st-scale__label-text">{{ $scaleOptions->last()->label }}</span>
                        </div>
                        @endif

                        <div class="st-scale__options">
                            @foreach ($scaleOptions as $opt)
                            <label class="st-scale-opt" for="q{{ $question->id }}_v{{ $opt->value }}" title="{{ $opt->label }}">
                                <span class="st-scale-opt__value">{{ $opt->value }}</span>
                                <input type="radio"
                                       id="q{{ $question->id }}_v{{ $opt->value }}"
                                       name="responses[{{ $question->id }}]"
                                       value="{{ $opt->value }}"
                                       class="st-scale-opt__radio rating-input"
                                       data-question="{{ $question->id }}"
                                       {{ old("responses.{$question->id}") == $opt->value ? 'checked' : '' }}>
                                @if ($hasLabels && $scaleOptions->count() <= 6)
                                    <span class="st-scale-opt__label">{{ $opt->label }}</span>
                                @endif
                            </label>
                            @endforeach
                        </div>

                    </div>

                {{-- ── TEXT: Open-ended ── --}}
                @else
                    <textarea
                        name="responses[{{ $question->id }}]"
                        class="st-text-answer"
                        placeholder="Your answer"
                        rows="2"
                        maxlength="2000"
                        data-question="{{ $question->id }}"
                    >{{ old("responses.{$question->id}") }}</textarea>
                    <div class="st-char-count" id="cc-{{ $question->id }}">0 / 2000</div>
                @endif

                @error("responses.{$question->id}")
                    <p class="st-error">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror

            </div>
            @endforeach

        </div>
        @endforeach

    </form>

</main>

{{-- ===== STICKY SUBMIT BAR ===== --}}
<div class="st-submit-bar">
    <a href="{{ route('survey.index') }}" class="st-submit-bar__cancel">Cancel</a>

    <div class="st-notify" id="notifyToggles" aria-label="Notification preferences">
        {{-- Email toggle --}}
        <div class="d-flex align-items-center gap-2">
            <input type="hidden" name="notify_email" value="0" form="surveyForm">
            <label class="nt-switch" for="notify_email">
                <input type="checkbox" id="notify_email" name="notify_email" value="1"
                       class="nt-switch__input" form="surveyForm">
                <span class="nt-switch__track"></span>
                <span class="nt-switch__thumb"></span>
            </label>
            <label for="notify_email" class="nt-label">
                <i class="bi bi-envelope me-1"></i>Email confirmation
            </label>
        </div>
        {{-- Dashboard toggle --}}
        <div class="d-flex align-items-center gap-2">
            <input type="hidden" name="notify_dashboard" value="0" form="surveyForm">
            <label class="nt-switch" for="notify_dashboard">
                <input type="checkbox" id="notify_dashboard" name="notify_dashboard" value="1"
                       class="nt-switch__input" form="surveyForm">
                <span class="nt-switch__track"></span>
                <span class="nt-switch__thumb"></span>
            </label>
            <label for="notify_dashboard" class="nt-label">
                <i class="bi bi-bell me-1"></i>Dashboard notification
            </label>
        </div>
    </div>

    <div class="st-submit-bar__spacer"></div>

    <span class="st-submit-bar__count" id="answeredCount">
        0 / {{ $survey->questions->where('question_type', 'rating')->count() }} required
    </span>

    <button type="submit" form="surveyForm"
            class="btn btn-primary st-submit-bar__btn"
            id="submitBtn">
        <i class="bi bi-send me-1"></i> Submit
    </button>
</div>

<script>
(function () {
    // Theme
    const saved = localStorage.getItem('cqi-theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', saved);

    // Progress
    const totalQ   = {{ $survey->questions->count() }};
    const totalReq = {{ $survey->questions->where('question_type', 'rating')->count() }};
    const fillEl   = document.getElementById('progressFill');
    const labelEl  = document.getElementById('progressLabel');
    const countEl  = document.getElementById('answeredCount');

    function updateProgress() {
        const answeredRating = document.querySelectorAll('.rating-input:checked').length;
        const answeredText   = [...document.querySelectorAll('.st-text-answer')]
                                   .filter(t => t.value.trim()).length;
        const total = answeredRating + answeredText;
        const pct   = totalQ > 0 ? Math.round((total / totalQ) * 100) : 0;

        if (fillEl)  fillEl.style.width  = pct + '%';
        if (labelEl) labelEl.textContent = total + ' / ' + totalQ + ' answered';
        if (countEl) countEl.textContent = answeredRating + ' / ' + totalReq + ' required';

        // Mark answered cards
        document.querySelectorAll('.rating-input').forEach(function (inp) {
            const card = document.getElementById('qcard-' + inp.dataset.question);
            if (!card) return;
            const groupAnswered = document.querySelector(
                '.rating-input[data-question="' + inp.dataset.question + '"]:checked'
            );
            if (groupAnswered) {
                card.classList.add('st-question--answered');
                card.classList.remove('st-question--error');
            }
        });

        document.querySelectorAll('.st-text-answer').forEach(function (el) {
            const card = document.getElementById('qcard-' + el.dataset.question);
            if (card && el.value.trim()) {
                card.classList.add('st-question--answered');
            } else if (card) {
                card.classList.remove('st-question--answered');
            }
        });
    }

    function updateCharCount(el) {
        const cc = document.getElementById('cc-' + el.dataset.question);
        if (cc) cc.textContent = el.value.length + ' / 2000';
        updateProgress();
    }

    document.querySelectorAll('.rating-input').forEach(function (i) {
        i.addEventListener('change', updateProgress);
    });
    document.querySelectorAll('.st-text-answer').forEach(function (el) {
        el.addEventListener('input', function () { updateCharCount(this); });
        if (el.value) updateCharCount(el);
    });

    updateProgress();

    // Submit loading state
    const form      = document.getElementById('surveyForm');
    const submitBtn = document.getElementById('submitBtn');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting…';
            submitBtn.disabled  = true;
        });
    }

    // ── Toggle switch animation ──
    // CSS sibling selectors handle track/thumb; JS only needs to update the label color.
    document.querySelectorAll('.nt-switch__input').forEach(function (checkbox) {
        function syncToggle() {
            const wrap  = checkbox.closest('.d-flex');
            if (!wrap) return;
            const label = wrap.querySelector('.nt-label');
            const track = checkbox.parentElement.querySelector('.nt-switch__track');
            const thumb = checkbox.parentElement.querySelector('.nt-switch__thumb');

            if (checkbox.checked) {
                if (track) track.style.background = 'var(--bs-primary, #1a73e8)';
                if (thumb) thumb.style.transform  = 'translateX(16px)';
                if (label) { label.classList.add('is-on'); }
            } else {
                if (track) track.style.background = '#e5e7eb';
                if (thumb) thumb.style.transform  = 'translateX(0)';
                if (label) { label.classList.remove('is-on'); }
            }
        }
        checkbox.addEventListener('change', syncToggle);
        syncToggle(); // init state on page load
    });

    // ── Move toggles into form on mobile (below 600px) ──
    const togglesEl = document.getElementById('notifyToggles');
    const formEl    = document.getElementById('surveyForm');

    function repositionToggles() {
        if (!togglesEl || !formEl) return;
        if (window.innerWidth < 600) {
            if (!formEl.contains(togglesEl)) {
                togglesEl.style.marginBottom = '1rem';
                togglesEl.style.flexWrap = 'wrap';
                togglesEl.style.gap = '.75rem';
                formEl.appendChild(togglesEl);
            }
        } else {
            const bar = document.querySelector('.st-submit-bar');
            if (bar && !bar.contains(togglesEl)) {
                const spacer = bar.querySelector('.st-submit-bar__spacer');
                bar.insertBefore(togglesEl, spacer || null);
                togglesEl.style.marginBottom = '';
            }
        }
    }

    repositionToggles();
    window.addEventListener('resize', repositionToggles);
})();
</script>

</body>
</html>
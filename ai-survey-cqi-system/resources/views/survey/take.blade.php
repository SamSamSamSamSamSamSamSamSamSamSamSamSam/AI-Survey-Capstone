<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }} — CQI System</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="survey-take-page">

{{-- ===== STICKY TOPBAR ===== --}}
<header class="survey-take-topbar">
    <a href="{{ route('survey.index') }}" class="survey-take-back">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="survey-take-topbar__title">{{ Str::limit($survey->title, 42) }}</div>

    <div class="survey-take-progress">
        <div class="survey-take-progress__track">
            <div class="survey-take-progress__fill" id="progressFill" style="width: 0%"></div>
        </div>
        <div class="survey-take-progress__label" id="progressLabel">
            0 / {{ $survey->questions->count() }} answered
        </div>
    </div>
</header>

{{-- ===== CONTENT ===== --}}
<main class="survey-take-content">

    {{-- Survey info card --}}
    <div class="survey-take-info-card">
        <h1 class="survey-take-info-card__title">{{ $survey->title }}</h1>
        @if ($survey->description)
            <p class="survey-take-info-card__desc">{{ $survey->description }}</p>
        @endif
        <div class="d-flex flex-wrap gap-2 mt-3">
            <span class="category-tag">
                <i class="bi bi-book me-1"></i>
                {{ $survey->offering->subject->course_code }} — {{ $survey->offering->subject->name }}
            </span>
            <span class="category-tag">
                <i class="bi bi-person me-1"></i>
                {{ $survey->offering->teacher->name }}
            </span>
            <span class="category-tag">
                <i class="bi bi-calendar3 me-1"></i>
                {{ $survey->offering->semester->full_label }}
            </span>
            <span class="category-tag">
                {{ $survey->questions->count() }} question(s)
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Please answer all required questions before submitting.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('survey.submit', $survey->id) }}" id="surveyForm">
        @csrf

        @foreach ($survey->questions as $question)
        @php $hasError = $errors->has("responses.{$question->id}"); @endphp

        <div class="survey-question-card {{ $hasError ? 'survey-question-card--error' : '' }}"
             id="qcard-{{ $question->id }}">

            {{-- Question meta --}}
            <div class="survey-question-card__meta">
                <span class="survey-question-card__num">Q{{ $question->order }}</span>
                @if ($question->category)
                    <span class="category-tag">{{ $question->category->name }}</span>
                @endif
                @if ($question->isRating())
                    <span class="question-type-badge question-type-badge--rating">Likert Scale</span>
                    <span class="survey-required-tag">* Required</span>
                @else
                    <span class="question-type-badge question-type-badge--open">Open-ended</span>
                    <span class="survey-optional-tag">Optional</span>
                @endif
            </div>

            {{-- Question text --}}
            <p class="survey-question-card__text">{{ $question->question_text }}</p>

            {{-- Answer input --}}
            @if ($question->isRating())
                @php $scaleOptions = $question->scale?->options ?? collect(); @endphp
                <div class="survey-scale-options">
                    @foreach ($scaleOptions as $opt)
                    <div class="survey-scale-option">
                        <input type="radio"
                               id="q{{ $question->id }}_v{{ $opt->value }}"
                               name="responses[{{ $question->id }}]"
                               value="{{ $opt->value }}"
                               class="survey-scale-option__input rating-input"
                               data-question="{{ $question->id }}"
                               {{ old("responses.{$question->id}") == $opt->value ? 'checked' : '' }}>
                        <label for="q{{ $question->id }}_v{{ $opt->value }}"
                               class="survey-scale-option__label">
                            <span class="survey-scale-option__val">{{ $opt->value }}</span>
                            <span class="survey-scale-option__lbl">{{ $opt->label }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>

            @else
                <textarea
                    name="responses[{{ $question->id }}]"
                    class="form-control survey-text-answer"
                    placeholder="Type your response here…"
                    maxlength="2000"
                    rows="3"
                    data-question="{{ $question->id }}"
                >{{ old("responses.{$question->id}") }}</textarea>
                <div class="survey-char-count" id="cc-{{ $question->id }}">0 / 2000</div>
            @endif

            @error("responses.{$question->id}")
                <p class="text-danger small mt-2">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </p>
            @enderror

        </div>
        @endforeach

    </form>

</main>

{{-- ===== STICKY SUBMIT BAR ===== --}}
<div class="survey-submit-bar">
    <a href="{{ route('survey.index') }}" class="survey-submit-bar__cancel">
        Cancel
    </a>
    <span class="survey-submit-bar__count" id="answeredCount">
        0 / {{ $survey->questions->where('question_type', 'rating')->count() }} required answered
    </span>
    <button type="submit" form="surveyForm" class="btn btn-primary survey-submit-bar__btn"
            id="submitBtn">
        <i class="bi bi-send me-1"></i> Submit Survey
    </button>
</div>

{{-- Theme persistence --}}
<script>
(function () {
    const saved = localStorage.getItem('cqi-theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', saved);

    // ---- Progress tracking ----
    const totalQ    = {{ $survey->questions->count() }};
    const totalReq  = {{ $survey->questions->where('question_type', 'rating')->count() }};
    const fillEl    = document.getElementById('progressFill');
    const labelEl   = document.getElementById('progressLabel');
    const countEl   = document.getElementById('answeredCount');

    function updateProgress() {
        const answeredRating = document.querySelectorAll('.rating-input:checked').length;
        const answeredText   = [...document.querySelectorAll('.survey-text-answer')]
                                   .filter(t => t.value.trim()).length;
        const total = answeredRating + answeredText;
        const pct   = totalQ > 0 ? Math.round((total / totalQ) * 100) : 0;

        if (fillEl)  fillEl.style.width  = pct + '%';
        if (labelEl) labelEl.textContent = total + ' / ' + totalQ + ' answered';
        if (countEl) countEl.textContent = answeredRating + ' / ' + totalReq + ' required answered';

        // Highlight card when answered
        document.querySelectorAll('.rating-input:checked').forEach(function (inp) {
            const card = document.getElementById('qcard-' + inp.dataset.question);
            if (card) card.classList.add('survey-question-card--answered');
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
    document.querySelectorAll('.survey-text-answer').forEach(function (el) {
        el.addEventListener('input', function () { updateCharCount(this); });
        if (el.value) updateCharCount(el);
    });

    updateProgress();

    // Submit button loading state
    const form      = document.getElementById('surveyForm');
    const submitBtn = document.getElementById('submitBtn');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting…';
            submitBtn.disabled = true;
        });
    }
})();
</script>

</body>
</html>
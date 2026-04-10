<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }} — CQI System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; min-height: 100vh; }

        .topbar { background: #fff; padding: .85rem 1.75rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); position: sticky; top: 0; z-index: 10; }
        .topbar h1 { font-size: .95rem; font-weight: 600; color: #374151; }
        .back-link { font-size: .85rem; color: #4f46e5; text-decoration: none; }
        .progress-wrap { flex: 1; max-width: 300px; margin: 0 1.5rem; }
        .progress-bar { height: 6px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
        .progress-fill { height: 100%; background: #4f46e5; border-radius: 999px; transition: width .3s; }
        .progress-label { font-size: .72rem; color: #6b7280; text-align: right; margin-top: .25rem; }

        .content { max-width: 720px; margin: 0 auto; padding: 2rem 1rem 5rem; }

        .survey-header { background: #fff; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #4f46e5; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .survey-header h2 { font-size: 1.1rem; margin-bottom: .35rem; }
        .survey-header p  { font-size: .875rem; color: #6b7280; line-height: 1.6; }
        .survey-meta { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; font-size: .78rem; }
        .meta-chip { background: #f3f4f6; padding: .2rem .55rem; border-radius: 4px; color: #6b7280; }

        .question-card { background: #fff; border-radius: 10px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 2px solid transparent; transition: border-color .2s; }
        .question-card:focus-within { border-color: #a5b4fc; }
        .question-card.has-error { border-color: #fca5a5; }

        .q-meta { display: flex; align-items: center; gap: .5rem; margin-bottom: .6rem; flex-wrap: wrap; }
        .q-number   { font-size: .72rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
        .q-category { font-size: .72rem; background: #f3f4f6; padding: .15rem .45rem; border-radius: 4px; color: #6b7280; }
        .q-type-rating { font-size: .7rem; padding: .15rem .45rem; border-radius: 4px; font-weight: 600; background: #dbeafe; color: #1d4ed8; }
        .q-type-text   { font-size: .7rem; padding: .15rem .45rem; border-radius: 4px; font-weight: 600; background: #f3e8ff; color: #7e22ce; }
        .q-required { color: #dc2626; font-size: .72rem; margin-left: auto; }

        .q-text { font-size: .95rem; font-weight: 500; color: #111; line-height: 1.5; margin-bottom: 1rem; }

        /* Dynamic scale options */
        .scale-options { display: flex; gap: .5rem; flex-wrap: wrap; }
        .scale-option input[type="radio"] { display: none; }
        .scale-option label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-width: 64px; padding: .65rem .5rem; border: 2px solid #e5e7eb; border-radius: 8px;
            cursor: pointer; transition: all .15s; font-size: .85rem; font-weight: 600; color: #6b7280; text-align: center;
        }
        .scale-option label .scale-label { font-size: .65rem; font-weight: 400; margin-top: .2rem; line-height: 1.2; }
        .scale-option input:checked + label { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .scale-option label:hover { border-color: #a5b4fc; background: #eff0ff; }
        .scale-option input:checked + label:hover { background: #4338ca; }

        .text-answer { width: 100%; padding: .7rem .9rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: .9rem; resize: vertical; min-height: 100px; font-family: inherit; transition: border-color .15s; }
        .text-answer:focus { outline: none; border-color: #6366f1; }
        .char-count { font-size: .75rem; color: #9ca3af; text-align: right; margin-top: .3rem; }

        .error-msg { color: #dc2626; font-size: .8rem; margin-top: .5rem; }

        .submit-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #e5e7eb; padding: 1rem 1.75rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -2px 8px rgba(0,0,0,.06); }
        .answered-count { font-size: .85rem; color: #6b7280; }
        .btn-submit { padding: .65rem 2rem; background: #4f46e5; color: #fff; border: none; border-radius: 7px; font-size: .95rem; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #4338ca; }
        .btn-cancel { text-decoration: none; font-size: .85rem; color: #6b7280; }

        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; padding: .85rem 1rem; margin-bottom: 1.25rem; font-size: .875rem; }
    </style>
</head>
<body>

<div class="topbar">
    <a href="{{ route('survey.index') }}" class="back-link">← Back</a>
    <h1>{{ $survey->title }}</h1>
    <div class="progress-wrap">
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill" style="width:0%"></div>
        </div>
        <div class="progress-label" id="progress-label">0 / {{ $survey->questions->count() }} answered</div>
    </div>
</div>

<div class="content">

    <div class="survey-header">
        <h2>{{ $survey->title }}</h2>
        @if ($survey->description)
            <p>{{ $survey->description }}</p>
        @endif
        <div class="survey-meta">
            <span class="meta-chip">📚 {{ $survey->offering->subject->course_code }} — {{ $survey->offering->subject->name }}</span>
            <span class="meta-chip">👤 {{ $survey->offering->teacher->name }}</span>
            <span class="meta-chip">🗓 {{ $survey->offering->semester->full_label }}</span>
            <span class="meta-chip">{{ $survey->questions->count() }} question(s)</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-error">Please answer all required questions before submitting.</div>
    @endif

    <form method="POST" action="{{ route('survey.submit', $survey->id) }}" id="survey-form">
        @csrf

        @foreach ($survey->questions as $question)
        @php $hasError = $errors->has("responses.{$question->id}"); @endphp

        <div class="question-card {{ $hasError ? 'has-error' : '' }}">

            <div class="q-meta">
                <span class="q-number">Question {{ $question->order_number }}</span>
                @if ($question->category)
                    <span class="q-category">{{ $question->category->name }}</span>
                @endif
                @if ($question->isRating())
                    <span class="q-type-rating">Likert Scale</span>
                    <span class="q-required">* Required</span>
                @else
                    <span class="q-type-text">Open-ended</span>
                    <span style="font-size:.72rem;color:#9ca3af;margin-left:auto;">Optional</span>
                @endif
            </div>

            <div class="q-text">{{ $question->question_text }}</div>

            @if ($question->isRating())
                {{-- Dynamic scale options from DB --}}
                @php $scaleOptions = $question->scale?->options ?? collect(); @endphp
                <div class="scale-options">
                    @foreach ($scaleOptions as $opt)
                    <div class="scale-option">
                        <input type="radio"
                               id="q{{ $question->id }}_v{{ $opt->value }}"
                               name="responses[{{ $question->id }}]"
                               value="{{ $opt->value }}"
                               {{ old("responses.{$question->id}") == $opt->value ? 'checked' : '' }}
                               data-question="{{ $question->id }}"
                               class="rating-input">
                        <label for="q{{ $question->id }}_v{{ $opt->value }}">
                            {{ $opt->value }}
                            <span class="scale-label">{{ $opt->label }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>

            @else
                <textarea
                    name="responses[{{ $question->id }}]"
                    class="text-answer"
                    placeholder="Type your response here…"
                    maxlength="2000"
                    data-question="{{ $question->id }}"
                    oninput="updateCharCount(this)"
                >{{ old("responses.{$question->id}") }}</textarea>
                <div class="char-count" id="cc-{{ $question->id }}">0 / 2000</div>
            @endif

            @error("responses.{$question->id}")
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>
        @endforeach
    </form>
</div>

<div class="submit-bar">
    <a href="{{ route('survey.index') }}" class="btn-cancel">Cancel</a>
    <span class="answered-count" id="answered-count">
        0 / {{ $survey->questions->where('question_type', 'rating')->count() }} required answered
    </span>
    <button type="submit" form="survey-form" class="btn-submit">Submit Survey</button>
</div>

<script>
const totalQuestions = {{ $survey->questions->count() }};
const totalRequired  = {{ $survey->questions->where('question_type', 'rating')->count() }};

function updateProgress() {
    const answeredRating = document.querySelectorAll('.rating-input:checked').length;
    const answeredText   = [...document.querySelectorAll('.text-answer')].filter(t => t.value.trim()).length;
    const total          = answeredRating + answeredText;
    const pct            = totalQuestions > 0 ? Math.round((total / totalQuestions) * 100) : 0;

    document.getElementById('progress-fill').style.width  = pct + '%';
    document.getElementById('progress-label').textContent = total + ' / ' + totalQuestions + ' answered';
    document.getElementById('answered-count').textContent = answeredRating + ' / ' + totalRequired + ' required answered';
}

function updateCharCount(el) {
    const cc = document.getElementById('cc-' + el.dataset.question);
    if (cc) cc.textContent = el.value.length + ' / 2000';
    updateProgress();
}

document.querySelectorAll('.rating-input').forEach(i => i.addEventListener('change', updateProgress));
document.querySelectorAll('.text-answer').forEach(el => { if (el.value) updateCharCount(el); });

updateProgress();
</script>
</body>
</html>

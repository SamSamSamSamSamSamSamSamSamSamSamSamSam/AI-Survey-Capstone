<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Surveys — CQI System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; min-height: 100vh; }

        .topbar { background: #fff; padding: .85rem 1.75rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .topbar-left h1 { font-size: 1rem; font-weight: 600; }
        .topbar-meta { font-size: .82rem; color: #6b7280; }
        .topbar-right { display: flex; align-items: center; gap: .75rem; font-size: .85rem; color: #6b7280; }

        .back-link { font-size: .85rem; color: #4f46e5; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .btn-logout { background: none; border: 1px solid #e5e7eb; padding: .35rem .85rem; border-radius: 6px; font-size: .8rem; cursor: pointer; color: #374151; }

        .content { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }

        .page-header { margin-bottom: 1.5rem; }
        .page-header h2 { font-size: 1.25rem; margin-bottom: .25rem; }
        .page-header p  { font-size: .875rem; color: #6b7280; }

        /* Section tabs */
        .section-tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb; }
        .tab { padding: .5rem 1rem; font-size: .875rem; font-weight: 500; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; }
        .tab.active { color: #4f46e5; border-bottom-color: #4f46e5; }

        /* Survey cards */
        .survey-grid { display: grid; gap: 1rem; }
        .survey-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
        .survey-card.completed { opacity: .65; }
        .survey-card:hover:not(.completed) { border-color: #a5b4fc; box-shadow: 0 2px 10px rgba(99,102,241,.08); }

        .survey-info { flex: 1; }
        .survey-title { font-weight: 600; font-size: 1rem; margin-bottom: .3rem; }
        .survey-desc  { font-size: .85rem; color: #6b7280; margin-bottom: .65rem; line-height: 1.5; }
        .survey-meta  { display: flex; flex-wrap: wrap; gap: .5rem; font-size: .78rem; color: #6b7280; align-items: center; }
        .meta-chip { background: #f3f4f6; padding: .15rem .55rem; border-radius: 4px; }

        .survey-action { flex-shrink: 0; }
        .btn-take { display: inline-block; padding: .55rem 1.25rem; background: #4f46e5; color: #fff; border-radius: 7px; font-size: .875rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: background .15s; }
        .btn-take:hover { background: #4338ca; }
        .btn-done { display: inline-block; padding: .55rem 1.25rem; background: #d1fae5; color: #065f46; border-radius: 7px; font-size: .875rem; font-weight: 600; }

        .empty-state { text-align: center; padding: 3rem; color: #9ca3af; background: #fff; border-radius: 10px; border: 1px solid #e5e7eb; }
        .alert { padding: .7rem 1rem; border-radius: 7px; font-size: .875rem; margin-bottom: 1.25rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

        .count-badge { display: inline-block; background: #e0e7ff; color: #3730a3; font-size: .72rem; font-weight: 700; padding: .1rem .45rem; border-radius: 999px; margin-left: .4rem; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <a href="{{ auth()->user()->dashboardRoute() }}" class="back-link">← Dashboard</a>
        <h1>My Surveys</h1>
    </div>
    <div class="topbar-right">
        <span class="topbar-meta">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Sign Out</button>
        </form>
    </div>
</div>

<div class="content">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="page-header">
        <h2>Available Surveys</h2>
        <p>Complete the surveys below to share your feedback. Your responses are recorded anonymously.</p>
    </div>

    @php
        $pending   = $surveys->filter(fn ($s) => ! in_array($s->id, $attemptedIds));
        $completed = $surveys->filter(fn ($s) => in_array($s->id, $attemptedIds));
    @endphp

    {{-- Tabs --}}
    <div class="section-tabs">
        <div class="tab active" onclick="showTab('pending', this)">
            Pending
            @if ($pending->count()) <span class="count-badge">{{ $pending->count() }}</span> @endif
        </div>
        <div class="tab" onclick="showTab('completed', this)">
            Completed
            @if ($completed->count()) <span class="count-badge">{{ $completed->count() }}</span> @endif
        </div>
    </div>

    {{-- Pending --}}
    <div id="tab-pending">
        @if ($pending->isEmpty())
            <div class="empty-state">🎉 No pending surveys. You're all caught up!</div>
        @else
            <div class="survey-grid">
                @foreach ($pending as $survey)
                <div class="survey-card">
                    <div class="survey-info">
                        <div class="survey-title">{{ $survey->title }}</div>
                        @if ($survey->description)
                            <div class="survey-desc">{{ $survey->description }}</div>
                        @endif
                        <div class="survey-meta">
                            <span class="meta-chip">📚 {{ $survey->offering->subject->course_code }}</span>
                            <span class="meta-chip">👤 {{ $survey->offering->teacher->name }}</span>
                            <span class="meta-chip">🗓 {{ $survey->offering->semester->full_label }}</span>
                            <span class="meta-chip">{{ $survey->questions->count() }} question(s)</span>
                        </div>
                    </div>
                    <div class="survey-action">
                        <a href="{{ route('survey.take', $survey->id) }}" class="btn-take">Take Survey</a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Completed --}}
    <div id="tab-completed" style="display:none;">
        @if ($completed->isEmpty())
            <div class="empty-state">No completed surveys yet.</div>
        @else
            <div class="survey-grid">
                @foreach ($completed as $survey)
                <div class="survey-card completed">
                    <div class="survey-info">
                        <div class="survey-title">{{ $survey->title }}</div>
                        <div class="survey-meta">
                            <span class="meta-chip">📚 {{ $survey->offering->subject->course_code }}</span>
                            <span class="meta-chip">👤 {{ $survey->offering->teacher->name }}</span>
                            <span class="meta-chip">🗓 {{ $survey->offering->semester->full_label }}</span>
                        </div>
                    </div>
                    <div class="survey-action">
                        <span class="btn-done">✓ Submitted</span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
function showTab(name, el) {
    document.getElementById('tab-pending').style.display   = 'none';
    document.getElementById('tab-completed').style.display = 'none';
    document.getElementById('tab-' + name).style.display   = 'block';
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}
</script>

</body>
</html>

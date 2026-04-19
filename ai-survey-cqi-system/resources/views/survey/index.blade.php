@extends('layouts.app')
@section('title', 'My Surveys')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url(auth()->user()->dashboardRoute()) }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Surveys</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Available Surveys</h2>
        <p class="page-subheading">
            Complete the surveys below to share your feedback.
            Your responses are anonymous.
        </p>
    </div>
</div>

@php
    $pending   = $surveys->filter(fn($s) => ! in_array($s->id, $attemptedIds));
    $completed = $surveys->filter(fn($s) => in_array($s->id, $attemptedIds));
@endphp

{{-- Role-specific context banner --}}
@if ($user->hasRole('faculty'))
    <div class="alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;">
        <strong>Peer Evaluation Surveys</strong> — these are courses taught by other faculty that you are invited to evaluate.
        You will not see surveys for courses you are assigned to teach.
    </div>
@elseif ($user->hasRole('admin'))
    <div class="alert" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;">
        <strong>Admin Surveys</strong> — surveys targeted at administrators for your response.
    </div>
@endif

{{-- ===== TABS ===== --}}
<div class="survey-tabs mb-3">
    <button class="survey-tab survey-tab--active" data-tab="pending">
        Pending
        @if ($pending->count())
            <span class="survey-tab__badge">{{ $pending->count() }}</span>
        @endif
    </button>
    <button class="survey-tab" data-tab="completed">
        Completed
        @if ($completed->count())
            <span class="survey-tab__badge survey-tab__badge--done">{{ $completed->count() }}</span>
        @endif
    </button>
</div>

{{-- ===== PENDING ===== --}}
<div id="tab-pending">
    @if ($pending->isEmpty())
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon" style="background: rgba(34,197,94,.1); color: #16a34a;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <p class="empty-state-text">
                    No pending surveys — you're all caught up!
                </p>
            </div>
        </div>
    @else
        <div class="student-survey-list">
            @foreach ($pending as $survey)
            <div class="student-survey-card">
                <div class="student-survey-card__body">
                    <div class="fw-500 mb-1" style="font-size:.9375rem;">{{ $survey->title }}</div>
                    @if ($survey->description)
                        <p class="text-muted-sm mb-2" style="line-height:1.5;">{{ $survey->description }}</p>
                    @endif
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="category-tag">
                            <i class="bi bi-book me-1"></i>{{ $survey->offering->subject->course_code }}
                        </span>
                        <span class="category-tag">
                            <i class="bi bi-person me-1"></i>{{ $survey->offering->teacher->name }}
                        </span>
                        <span class="category-tag">
                            <i class="bi bi-calendar3 me-1"></i>{{ $survey->offering->semester->full_label }}
                        </span>
                        <span class="category-tag">
                            {{ $survey->questions->count() }} question(s)
                        </span>
                    </div>
                </div>
                <a href="{{ route('survey.take', $survey->id) }}"
                   class="btn btn-primary btn-sm flex-shrink-0">
                    Take Survey <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ===== COMPLETED ===== --}}
<div id="tab-completed" style="display: none;">
    @if ($completed->isEmpty())
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-clipboard-check"></i></div>
                <p class="empty-state-text">No completed surveys yet.</p>
            </div>
        </div>
    @else
        <div class="student-survey-list">
            @foreach ($completed as $survey)
            <div class="student-survey-card student-survey-card--done">
                <div class="student-survey-card__body">
                    <div class="fw-500 mb-1" style="font-size:.9375rem;">{{ $survey->title }}</div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="category-tag">
                            <i class="bi bi-book me-1"></i>{{ $survey->offering->subject->course_code }}
                        </span>
                        <span class="category-tag">
                            <i class="bi bi-person me-1"></i>{{ $survey->offering->teacher->name }}
                        </span>
                        <span class="category-tag">
                            <i class="bi bi-calendar3 me-1"></i>{{ $survey->offering->semester->full_label }}
                        </span>
                    </div>
                </div>
                <span class="survey-done-badge flex-shrink-0">
                    <i class="bi bi-check-circle-fill me-1"></i> Submitted
                </span>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    const tabs    = document.querySelectorAll('.survey-tab');
    const pending = document.getElementById('tab-pending');
    const done    = document.getElementById('tab-completed');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('survey-tab--active'));
            this.classList.add('survey-tab--active');
            const which = this.dataset.tab;
            pending.style.display = which === 'pending'   ? '' : 'none';
            done.style.display    = which === 'completed' ? '' : 'none';
        });
    });
})();
</script>
@endpush
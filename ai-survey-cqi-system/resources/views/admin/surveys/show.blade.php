@extends('layouts.app')
@section('title', $survey->title)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($survey->title, 30) }}</li>
</ol>
@endsection

@section('content')

{{-- ===== PAGE HEADER ===== --}}
<div class="page-header flex-wrap gap-2">
    <div>
        <h2 class="page-heading">{{ $survey->title }}</h2>
        <p class="page-subheading">
            {{ $survey->offering->subject->course_code }} —
            {{ $survey->offering->subject->name }} ·
            {{ $survey->offering->semester->full_label }}
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.surveys.toggle-active', $survey->id) }}">
            @csrf @method('PATCH')
            <button type="submit"
                    class="btn btn-sm {{ $survey->is_active ? 'btn-warning' : 'btn-success' }}">
                <i class="bi bi-{{ $survey->is_active ? 'pause-circle' : 'play-circle' }} me-1"></i>
                {{ $survey->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ $survey->is_active ? '#' : route('admin.surveys.edit', $survey->id) }}" 
            class="btn btn-sm btn-outline-secondary {{ $survey->is_active ? 'disabled' : '' }}" 
            title="{{ $survey->is_active ? 'Cannot edit while active' : 'Edit' }}"
            style="{{ $survey->is_active ? 'pointer-events: auto; cursor: not-allowed;' : '' }}">
            
            <i class="bi {{ $survey->is_active ? 'bi-lock-fill text-muted' : 'bi-pencil me-1' }}"></i> Edit
        </a>
        <a href="{{ route('admin.surveys.attempts', $survey->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-chat-left-text me-1"></i> Responses
            <span class="ms-1 badge text-bg-secondary" style="font-size:.65rem;">{{ $submittedCount }}</span>
        </a>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== STATUS BANNER ===== --}}
@if ($survey->is_active)
    <div class="survey-banner survey-banner--active">
        <i class="bi bi-check-circle-fill"></i>
        This survey is <strong>active</strong> and currently accepting responses.
    </div>
@else
    <div class="survey-banner survey-banner--inactive">
        <i class="bi bi-pause-circle-fill"></i>
        This survey is <strong>inactive</strong>. Activate it once all questions are ready.
    </div>
@endif

{{-- ===== INFO + STATS GRID ===== --}}
<div class="survey-meta-grid mb-4">

    {{-- Info card --}}
    <div class="card">
        <div class="card-body">
            <p class="card-section-label">Survey Info</p>
            <div class="detail-row">
                <span class="detail-label"><i class="bi bi-book me-2 text-muted"></i>Offering</span>
                <span class="detail-value">
                    {{ $survey->offering->subject->course_code }} —
                    {{ $survey->offering->subject->name }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="bi bi-people-fill me-2 text-muted"></i>Group</span>
                <span class="detail-value">{{ $survey->offering->group_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="bi bi-calendar3 me-2 text-muted"></i>Semester</span>
                <span class="detail-value">{{ $survey->offering->semester->full_label }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="bi bi-person-workspace me-2 text-muted"></i>Faculty</span>
                <span class="detail-value">{{ $survey->offering->teacher->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="bi bi-people me-2 text-muted"></i>Target</span>
                <span class="detail-value">
                    <span class="role-pill role-pill--{{ $survey->targetRole->name }}">
                        {{ ucfirst($survey->targetRole->name) }}
                    </span>
                </span>
            </div>
            <div class="detail-row detail-row--last">
                <span class="detail-label"><i class="bi bi-person-check me-2 text-muted"></i>Created By</span>
                <span class="detail-value">{{ $survey->creator->name }}</span>
            </div>
            @if ($survey->description)
                <p class="survey-description">{{ $survey->description }}</p>
            @endif
        </div>
    </div>

    {{-- Stats card --}}
    <div class="card">
        <div class="card-body">
            <p class="card-section-label">Response Summary</p>
            <div class="survey-stats">
                <div class="survey-stat">
                    <span class="survey-stat__value">{{ $submittedCount }}</span>
                    <span class="survey-stat__label">Responses</span>
                </div>
                <div class="survey-stat-divider"></div>
                <div class="survey-stat">
                    <span class="survey-stat__value survey-stat__value--muted">
                        {{ $survey->questions->count() }}
                    </span>
                    <span class="survey-stat__label">Questions</span>
                </div>
            </div>

            @if ($submittedCount > 0)
                <a href="{{ route('admin.surveys.attempts', $survey->id) }}"
                   class="btn btn-primary btn-sm w-100 mt-3">
                    <i class="bi bi-eye me-1"></i> View All Responses
                </a>
            @endif
        </div>
    </div>

</div>

{{-- ===== QUESTIONS & WEIGHT CONFIGURATION TABS ===== --}}
<div>
    <ul class="nav nav-tabs mb-4" id="surveyManagementTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="survey-questions-tab" data-bs-toggle="tab" data-bs-target="#survey-questions-pane" type="button" role="tab" aria-controls="survey-questions-pane" aria-selected="true">
                <i class="bi bi-list-check me-2"></i>Questions
                <span class="badge bg-secondary ms-1">{{ $survey->questions->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="survey-weights-tab" data-bs-toggle="tab" data-bs-target="#survey-weights-pane" type="button" role="tab" aria-controls="survey-weights-pane" aria-selected="false">
                <i class="bi bi-sliders me-2"></i>Categories
            </button>
        </li>
    </ul>

    <div class="tab-content" id="surveyManagementTabsContent">
        
        {{-- TAB 1: Questions Table --}}
        <div class="tab-pane fade show active" id="survey-questions-pane" role="tabpanel" aria-labelledby="survey-questions-tab" tabindex="0">
            
            {{-- Questions Table Action Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <p class="card-section-label mb-0">
                    Questions List
                </p>
                @if (! $survey->is_active)
                    <a href="{{ route('admin.surveys.questions.create', $survey->id) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Question
                    </a>
                @endif
            </div>

            @if ($survey->questions->isEmpty())
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-question-circle"></i></div>
                        <p class="empty-state-text">No questions yet.</p>
                        <a href="{{ route('admin.surveys.questions.create', $survey->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add First Question
                        </a>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="table-responsive">
                        <table class="table data-table align-middle mb-0" id="question-table">
                            <thead>
                                <tr>
                                    @if (! $survey->is_active)
                                        <th style="width: 48px;"></th>
                                    @endif
                                    <th style="width: 48px;">#</th>
                                    <th>Question</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    @if (! $survey->is_active)
                                        <th class="text-end">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="sortable-questions">
                                @foreach ($survey->questions as $question)
                                <tr data-id="{{ $question->id }}">

                                    @if (! $survey->is_active)
                                        <td class="drag-handle" title="Drag to reorder">
                                            <i class="bi bi-grip-vertical text-muted"></i>
                                        </td>
                                    @endif

                                    <td class="text-muted-sm question-order">{{ $question->order }}</td>

                                    <td class="fw-500" style="font-size:.875rem; max-width: 380px;">
                                        {{ $question->question_text }}
                                    </td>

                                    <td>
                                        @if ($question->category)
                                            <span class="category-tag">{{ $question->category->name }}</span>
                                        @else
                                            <span class="text-muted-sm">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($question->isRating())
                                            <span class="question-type-badge question-type-badge--rating">
                                                <i class="bi bi-bar-chart-line me-1"></i>Likert (1–5)
                                            </span>
                                        @else
                                            <span class="question-type-badge question-type-badge--open">
                                                <i class="bi bi-chat-text me-1"></i>Open-ended
                                            </span>
                                        @endif
                                    </td>

                                    @if (! $survey->is_active)
                                    <td class="text-end">
                                        <div class="table-actions">
                                            <a href="{{ route('admin.surveys.questions.edit', [$survey->id, $question->id]) }}"
                                               class="btn btn-sm btn-icon" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('admin.surveys.questions.destroy', [$survey->id, $question->id]) }}"
                                                  class="d-inline"
                                                  data-confirm="Delete this question permanently?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (! $survey->is_active && $survey->questions->count() > 1)
                        <div class="px-4 py-2 border-top" style="font-size: .75rem; color: var(--bs-secondary-color);">
                            <i class="bi bi-grip-vertical me-1"></i>
                            Drag rows to reorder questions. Order is saved automatically.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- TAB 2: Weight Configuration --}}
        <div class="tab-pane fade" id="survey-weights-pane" role="tabpanel" aria-labelledby="survey-weights-tab" tabindex="0">
            @php
                $weightQuestions = $survey->questions->load('category');
            @endphp
            @include('admin.surveys._weight_config', [
                'weightQuestions'   => $weightQuestions,
                'weightFormAction'  => route('admin.surveys.weights.save', $survey),
                'weightReadOnly'    => $survey->is_active,   // read-only when active
                'weightOwner'       => 'survey',
                'weightOwnerId'     => $survey->id,
            ])
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>

@if (! $survey->is_active && $survey->questions->count() > 1)
<script>
(function () {
    const tbody   = document.getElementById('sortable-questions');
    const reorderUrl = '{{ route('admin.surveys.questions.reorder', $survey->id) }}';
    const csrf    = '{{ csrf_token() }}';
    let dragged   = null;

    tbody.querySelectorAll('tr').forEach(function (row) {
        row.draggable = true;

        row.addEventListener('dragstart', function () {
            dragged = row;
            setTimeout(() => row.classList.add('dragging'), 0);
        });

        row.addEventListener('dragend', function () {
            row.classList.remove('dragging');
            saveOrder();
        });

        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            row.classList.add('drag-over');
        });

        row.addEventListener('dragleave', function () {
            row.classList.remove('drag-over');
        });

        row.addEventListener('drop', function (e) {
            e.preventDefault();
            row.classList.remove('drag-over');
            if (dragged && dragged !== row) {
                const rows = [...tbody.querySelectorAll('tr')];
                rows.indexOf(dragged) < rows.indexOf(row)
                    ? row.after(dragged)
                    : row.before(dragged);
                updateOrderCells();
            }
        });
    });

    function updateOrderCells() {
        tbody.querySelectorAll('tr').forEach(function (row, i) {
            const cell = row.querySelector('.question-order');
            if (cell) cell.textContent = i + 1;
        });
    }

    function saveOrder() {
        const order = [...tbody.querySelectorAll('tr')].map(function (r, i) {
            return { id: parseInt(r.dataset.id), order: i + 1 };
        });
        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ order }),
        });
    }
})();
</script>
@endif
@endpush
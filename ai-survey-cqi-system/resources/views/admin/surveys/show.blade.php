@extends('admin.layouts.app')
@section('title', $survey->title)

@section('content')
<div class="page-header">
    <h1>{{ $survey->title }}</h1>
    <div class="actions">
        <form method="POST" action="{{ route('admin.surveys.toggle-active', $survey->id) }}">
            @csrf @method('PATCH')
            <button class="btn {{ $survey->is_active ? 'btn-warning' : 'btn-success' }}">
                {{ $survey->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('admin.surveys.edit', $survey->id) }}" class="btn btn-secondary">Edit</a>
        <a href="{{ route('admin.surveys.attempts', $survey->id) }}" class="btn btn-secondary">View Responses ({{ $submittedCount }})</a>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

{{-- Status banner --}}
@if ($survey->is_active)
    <div class="alert alert-success">This survey is <strong>active</strong> and accepting responses.</div>
@else
    <div class="alert alert-info">This survey is <strong>inactive</strong>. Activate it when all questions are ready.</div>
@endif

{{-- Details --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
    <div class="card">
        <div class="card-body">
            <p class="form-text" style="margin-bottom:.5rem;">SURVEY INFO</p>
            <table style="font-size:.875rem;width:100%;">
                <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;width:130px;">Offering</td><td>{{ $survey->offering->subject->course_code }} — {{ $survey->offering->subject->name }}</td></tr>
                <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Semester</td><td>{{ $survey->offering->semester->full_label }}</td></tr>
                <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Faculty</td><td>{{ $survey->offering->teacher->name }}</td></tr>
                <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Target Role</td><td><span class="badge badge-{{ $survey->targetRole->name }}">{{ ucfirst($survey->targetRole->name) }}</span></td></tr>
                <tr><td style="color:#6b7280;padding:.3rem .5rem .3rem 0;">Created By</td><td>{{ $survey->creator->name }}</td></tr>
            </table>
            @if ($survey->description)
                <p style="margin-top:.75rem;font-size:.85rem;color:#374151;border-top:1px solid #f3f4f6;padding-top:.75rem;">{{ $survey->description }}</p>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="form-text" style="margin-bottom:.5rem;">RESPONSE SUMMARY</p>
            <div style="display:flex;gap:2rem;">
                <div>
                    <div style="font-size:2rem;font-weight:700;color:#4f46e5;">{{ $submittedCount }}</div>
                    <div style="font-size:.8rem;color:#6b7280;">Submitted</div>
                </div>
                <div>
                    <div style="font-size:2rem;font-weight:700;color:#6b7280;">{{ $survey->questions->count() }}</div>
                    <div style="font-size:.8rem;color:#6b7280;">Questions</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Questions --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
    <p class="form-text" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;">Questions</p>
    @if (! $survey->is_active)
        <a href="{{ route('admin.surveys.questions.create', $survey->id) }}" class="btn btn-sm btn-primary">+ Add Question</a>
    @endif
</div>

@if ($survey->questions->isEmpty())
    <div class="card">
        <p class="empty-state">No questions yet. <a href="{{ route('admin.surveys.questions.create', $survey->id) }}">Add the first question.</a></p>
    </div>
@else
    <div class="card" id="question-list">
        <table>
            <thead>
                <tr>
                    @if (! $survey->is_active)<th style="width:40px;">#</th>@endif
                    <th>Question</th>
                    <th>Category</th>
                    <th>Type</th>
                    @if (! $survey->is_active)<th>Actions</th>@endif
                </tr>
            </thead>
            <tbody id="sortable-questions">
                @foreach ($survey->questions as $question)
                <tr data-id="{{ $question->id }}" style="{{ ! $survey->is_active ? 'cursor:grab;' : '' }}">
                    @if (! $survey->is_active)
                        <td style="color:#9ca3af;">⠿ {{ $question->order }}</td>
                    @endif
                    <td>{{ $question->question_text }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $question->category ?? '—' }}</td>
                    <td>
                        @if ($question->isRating())
                            <span class="badge" style="background:#dbeafe;color:#1d4ed8;">Likert (1–5)</span>
                        @else
                            <span class="badge" style="background:#f3e8ff;color:#7e22ce;">Open-ended</span>
                        @endif
                    </td>
                    @if (! $survey->is_active)
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.surveys.questions.edit', [$survey->id, $question->id]) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.surveys.questions.destroy', [$survey->id, $question->id]) }}" onsubmit="return confirm('Delete this question?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
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
        <p class="form-text" style="margin-top:.5rem;">Drag rows to reorder questions.</p>
    @endif
@endif

{{-- Drag-to-reorder (only when inactive) --}}
@if (! $survey->is_active && $survey->questions->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('sortable-questions');
    let dragged = null;

    tbody.querySelectorAll('tr').forEach(row => {
        row.draggable = true;
        row.addEventListener('dragstart', () => { dragged = row; row.style.opacity = '.4'; });
        row.addEventListener('dragend',   () => { row.style.opacity = '1'; saveOrder(); });
        row.addEventListener('dragover',  e  => { e.preventDefault(); });
        row.addEventListener('drop', e => {
            e.preventDefault();
            if (dragged !== row) {
                const rows = [...tbody.querySelectorAll('tr')];
                const draggedIdx = rows.indexOf(dragged);
                const targetIdx  = rows.indexOf(row);
                draggedIdx < targetIdx
                    ? row.after(dragged)
                    : row.before(dragged);
            }
        });
    });

    function saveOrder() {
        const rows = [...tbody.querySelectorAll('tr')];
        const order = rows.map((r, i) => ({ id: parseInt(r.dataset.id), order: i + 1 }));

        fetch('{{ route('admin.surveys.questions.reorder', $survey->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ order }),
        });

        // Update visible order numbers
        rows.forEach((r, i) => {
            const cell = r.querySelector('td:first-child');
            if (cell) cell.textContent = '⠿ ' + (i + 1);
        });
    }
});
</script>
@endif
@endsection

@extends('admin.layouts.app')
@section('title', $surveyTemplate->name)

@section('content')
<div class="page-header">
    <h1>
        {{ $surveyTemplate->name }}
        @if ($surveyTemplate->is_official)
            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.72rem;vertical-align:middle;">⭐ Official</span>
        @endif
        @if ($surveyTemplate->is_active)
            <span class="badge badge-active" style="font-size:.72rem;vertical-align:middle;">Active</span>
        @else
            <span class="badge badge-inactive" style="font-size:.72rem;vertical-align:middle;">Inactive</span>
        @endif
    </h1>
    <div class="actions">
        <a href="{{ route('admin.survey-templates.edit', $surveyTemplate->id) }}" class="btn btn-secondary">Edit Details</a>
        <a href="{{ route('admin.survey-templates.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

@if ($surveyTemplate->description)
    <p style="color:#6b7280;font-size:.875rem;margin-bottom:1.25rem;">{{ $surveyTemplate->description }}</p>
@endif

{{-- Add question form --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">
        Add Question to Template
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.survey-templates.questions.store', $surveyTemplate->id) }}">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Question Text <span style="color:#dc2626">*</span></label>
                    <textarea name="question_text" rows="2" class="form-control {{ $errors->has('question_text') ? 'is-invalid' : '' }}"
                              placeholder="Enter the question…">{{ old('question_text') }}</textarea>
                    @error('question_text') <p class="invalid-feedback">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Type <span style="color:#dc2626">*</span></label>
                    <select name="question_type" id="tq-type" class="form-control" onchange="toggleTemplateScale(this.value)">
                        <option value="rating" @selected(old('question_type') === 'rating')>Likert Scale (Rating)</option>
                        <option value="text"   @selected(old('question_type') === 'text')>Open-ended (Text)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">— No Category —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="tq-scale-wrap">
                    <label class="form-label">Scale</label>
                    <select name="scale_id" class="form-control">
                        <option value="">— None —</option>
                        @foreach ($scales as $scale)
                            <option value="{{ $scale->id }}" @selected(old('scale_id') == $scale->id)>
                                {{ $scale->name }} ({{ $scale->min_value }}–{{ $scale->max_value }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Add Question</button>
        </form>
    </div>
</div>

{{-- Questions list --}}
<div class="card">
    <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;display:flex;justify-content:space-between;">
        <span>Questions ({{ $surveyTemplate->questions->count() }})</span>
        @if ($surveyTemplate->questions->count() > 1)
            <span style="font-size:.78rem;color:#9ca3af;font-weight:400;">Drag to reorder</span>
        @endif
    </div>

    @if ($surveyTemplate->questions->isEmpty())
        <p class="empty-state">No questions yet. Add one above.</p>
    @else
        <table>
            <thead>
                <tr><th style="width:40px;">#</th><th>Question</th><th>Type</th><th>Category</th><th>Scale</th><th>Actions</th></tr>
            </thead>
            <tbody id="tq-sortable">
                @foreach ($surveyTemplate->questions as $tq)
                <tr data-id="{{ $tq->id }}" style="cursor:grab;">
                    <td style="color:#9ca3af;">⠿ {{ $tq->order_number }}</td>
                    <td style="font-size:.875rem;">{{ $tq->question_text }}</td>
                    <td>
                        @if ($tq->isRating())
                            <span class="badge" style="background:#dbeafe;color:#1d4ed8;">Likert</span>
                        @else
                            <span class="badge" style="background:#f3e8ff;color:#7e22ce;">Open-ended</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;color:#6b7280;">{{ $tq->category?->name ?? '—' }}</td>
                    <td style="font-size:.8rem;color:#6b7280;">{{ $tq->scale?->name ?? '—' }}</td>
                    <td>
                        <div class="actions">
                            {{-- Inline edit via small modal-like approach --}}
                            <button onclick="openEditModal({{ $tq->id }}, '{{ addslashes($tq->question_text) }}', '{{ $tq->question_type }}', '{{ $tq->category_id }}', '{{ $tq->scale_id }}', {{ $tq->order_number }})"
                                    class="btn btn-sm btn-secondary">Edit</button>
                            <form method="POST" action="{{ route('admin.survey-templates.questions.destroy', [$surveyTemplate->id, $tq->id]) }}"
                                  onsubmit="return confirm('Remove this question?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Inline edit modal --}}
<div id="edit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;width:100%;max-width:540px;box-shadow:0 8px 32px rgba(0,0,0,.15);">
        <h3 style="font-size:1rem;margin-bottom:1rem;">Edit Question</h3>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Question Text</label>
                <textarea name="question_text" id="edit-text" rows="2" class="form-control"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="question_type" id="edit-type" class="form-control" onchange="toggleEditScale(this.value)">
                        <option value="rating">Likert Scale</option>
                        <option value="text">Open-ended</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="edit-category" class="form-control">
                        <option value="">— No Category —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="edit-scale-wrap">
                    <label class="form-label">Scale</label>
                    <select name="scale_id" id="edit-scale" class="form-control">
                        <option value="">— None —</option>
                        @foreach ($scales as $scale)
                            <option value="{{ $scale->id }}">{{ $scale->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Order</label>
                    <input type="number" name="order_number" id="edit-order" class="form-control" min="1">
                </div>
            </div>
            <div class="actions" style="margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Scale toggle on add form
function toggleTemplateScale(type) {
    document.getElementById('tq-scale-wrap').style.display = type === 'rating' ? '' : 'none';
}
toggleTemplateScale(document.getElementById('tq-type').value);

// Edit modal
function openEditModal(id, text, type, catId, scaleId, order) {
    document.getElementById('edit-form').action =
        '/admin/survey-templates/{{ $surveyTemplate->id }}/questions/' + id;
    document.getElementById('edit-text').value    = text;
    document.getElementById('edit-type').value    = type;
    document.getElementById('edit-category').value = catId || '';
    document.getElementById('edit-scale').value   = scaleId || '';
    document.getElementById('edit-order').value   = order;
    toggleEditScale(type);
    document.getElementById('edit-modal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}
function toggleEditScale(type) {
    document.getElementById('edit-scale-wrap').style.display = type === 'rating' ? '' : 'none';
}

// Drag to reorder
const tbody = document.getElementById('tq-sortable');
if (tbody) {
    let dragged = null;
    tbody.querySelectorAll('tr').forEach(row => {
        row.draggable = true;
        row.addEventListener('dragstart', () => { dragged = row; row.style.opacity = '.4'; });
        row.addEventListener('dragend',   () => { row.style.opacity = '1'; saveOrder(); });
        row.addEventListener('dragover',  e => e.preventDefault());
        row.addEventListener('drop', e => {
            e.preventDefault();
            if (dragged !== row) {
                const rows = [...tbody.querySelectorAll('tr')];
                rows.indexOf(dragged) < rows.indexOf(row) ? row.after(dragged) : row.before(dragged);
            }
        });
    });
    function saveOrder() {
        const rows = [...tbody.querySelectorAll('tr')];
        const order = rows.map((r, i) => ({ id: parseInt(r.dataset.id), order_number: i + 1 }));
        fetch('{{ route('admin.survey-templates.questions.reorder', $surveyTemplate->id) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ order }),
        });
        rows.forEach((r, i) => { r.querySelector('td').textContent = '⠿ ' + (i + 1); });
    }
}
</script>
@endsection

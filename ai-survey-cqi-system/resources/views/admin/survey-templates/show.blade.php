@extends('layouts.app')
@section('title', $surveyTemplate->name)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.survey-templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($surveyTemplate->name, 30) }}</li>
</ol>
@endsection

@section('content')

{{-- ===== PAGE HEADER ===== --}}
<div class="page-header">
    <div class="d-flex align-items-start gap-3">
        <div class="template-icon template-icon--lg">
            <i class="bi bi-layout-text-sidebar"></i>
        </div>
        <div>
            <h2 class="page-heading d-flex align-items-center gap-2 flex-wrap">
                {{ $surveyTemplate->name }}
                @if ($surveyTemplate->is_official)
                    <span class="official-badge"><i class="bi bi-star-fill me-1"></i>Official</span>
                @endif
                @if ($surveyTemplate->is_active)
                    <span class="status-pill status-pill--active">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </span>
                @else
                    <span class="status-pill status-pill--inactive">
                        <i class="bi bi-pause-circle me-1"></i>Inactive
                    </span>
                @endif
            </h2>
            @if ($surveyTemplate->description)
                <p class="page-subheading">{{ $surveyTemplate->description }}</p>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.survey-templates.edit', $surveyTemplate->id) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit Details
        </a>
        <a href="{{ route('admin.survey-templates.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ===== TWO-COLUMN LAYOUT ===== --}}
<div class="template-show-grid">

    {{-- ===== LEFT: Add question form ===== --}}
    <div>
        <div class="card">
            <div class="template-card-header">
                <i class="bi bi-plus-circle me-2 text-muted"></i>
                Add Question
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.survey-templates.questions.store', $surveyTemplate->id) }}"
                      novalidate>
                    @csrf

                    {{-- Question text --}}
                    <div class="mb-3">
                        <label class="form-label" for="question_text">
                            Question Text <span class="text-danger">*</span>
                        </label>
                        <textarea name="question_text" id="question_text" rows="2"
                                  class="form-control @error('question_text') is-invalid @enderror"
                                  placeholder="Enter the question…">{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label" for="tq-type">
                            Type <span class="text-danger">*</span>
                        </label>
                        <select name="question_type" id="tq-type" class="form-select">
                            <option value="rating" @selected(old('question_type') === 'rating')>
                                Likert Scale (Rating)
                            </option>
                            <option value="text" @selected(old('question_type') === 'text')>
                                Open-ended (Text)
                            </option>
                        </select>
                    </div>

                    {{-- Category --}}
                    <div class="mb-3">
                        <label class="form-label" for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">— No Category —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    @selected(old('category_id') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Scale (rating only) --}}
                    <div class="mb-3" id="tq-scale-wrap">
                        <label class="form-label" for="scale_id">Scale</label>
                        <select name="scale_id" id="scale_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($scales as $scale)
                                <option value="{{ $scale->id }}"
                                    @selected(old('scale_id') == $scale->id)>
                                    {{ $scale->name }} ({{ $scale->min_value }}–{{ $scale->max_value }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i> Add Question
                    </button>

                </form>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT: Questions list ===== --}}
    <div>
        <div class="card">
            <div class="template-card-header d-flex align-items-center justify-content-between">
                <span>
                    <i class="bi bi-list-check me-2 text-muted"></i>
                    Questions
                    <span class="ms-2 count-badge">{{ $surveyTemplate->questions->count() }}</span>
                </span>
                @if ($surveyTemplate->questions->count() > 1)
                    <span class="text-muted-sm">
                        <i class="bi bi-grip-vertical me-1"></i>Drag to reorder
                    </span>
                @endif
            </div>

            @if ($surveyTemplate->questions->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-question-circle"></i></div>
                    <p class="empty-state-text">No questions yet. Add one using the form.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table data-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;"></th>
                                <th style="width: 36px;">#</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Scale</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tq-sortable">
                            @foreach ($surveyTemplate->questions as $tq)
                            <tr data-id="{{ $tq->id }}">
                                <td class="drag-handle">
                                    <i class="bi bi-grip-vertical text-muted"></i>
                                </td>
                                <td class="text-muted-sm tq-order">{{ $tq->order_number }}</td>
                                <td style="font-size: .875rem; max-width: 260px;">
                                    {{ $tq->question_text }}
                                </td>
                                <td>
                                    @if ($tq->isRating())
                                        <span class="question-type-badge question-type-badge--rating">
                                            <i class="bi bi-bar-chart-line me-1"></i>Likert
                                        </span>
                                    @else
                                        <span class="question-type-badge question-type-badge--open">
                                            <i class="bi bi-chat-text me-1"></i>Open
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted-sm">{{ $tq->category?->name ?? '—' }}</td>
                                <td class="text-muted-sm">{{ $tq->scale?->name ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="table-actions">
                                        <button type="button"
                                                class="btn btn-sm btn-icon"
                                                title="Edit"
                                                data-tq-edit
                                                data-id="{{ $tq->id }}"
                                                data-text="{{ $tq->question_text }}"
                                                data-type="{{ $tq->question_type }}"
                                                data-category="{{ $tq->category_id }}"
                                                data-scale="{{ $tq->scale_id }}"
                                                data-order="{{ $tq->order_number }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('admin.survey-templates.questions.destroy', [$surveyTemplate->id, $tq->id]) }}"
                                              class="d-inline"
                                              data-confirm="Remove this question from the template?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>{{-- /.template-show-grid --}}

{{-- ===== EDIT QUESTION MODAL ===== --}}
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" style="font-size: .95rem; font-weight: 700;">
                    Edit Question
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="edit-q-form" method="POST" novalidate>
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="edit-text">Question Text</label>
                        <textarea name="question_text" id="edit-text" rows="3"
                                  class="form-control" required></textarea>
                    </div>

                    <div class="row g-3">

                        <div class="col-6">
                            <label class="form-label" for="edit-type">Type</label>
                            <select name="question_type" id="edit-type" class="form-select">
                                <option value="rating">Likert Scale</option>
                                <option value="text">Open-ended</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label" for="edit-category">Category</label>
                            <select name="category_id" id="edit-category" class="form-select">
                                <option value="">— No Category —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6" id="edit-scale-wrap">
                            <label class="form-label" for="edit-scale">Scale</label>
                            <select name="scale_id" id="edit-scale" class="form-select">
                                <option value="">— None —</option>
                                @foreach ($scales as $scale)
                                    <option value="{{ $scale->id }}">{{ $scale->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label" for="edit-order">Order</label>
                            <input type="number" name="order_number" id="edit-order"
                                   class="form-control" min="1">
                        </div>

                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="edit-q-save">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
<script>
(function () {
    'use strict';

    const templateId  = {{ $surveyTemplate->id }};
    const csrf        = '{{ csrf_token() }}';
    const reorderUrl  = '{{ route('admin.survey-templates.questions.reorder', $surveyTemplate->id) }}';

    // ---- Scale visibility toggle (add form) ----
    const addTypeSelect = document.getElementById('tq-type');
    const addScaleWrap  = document.getElementById('tq-scale-wrap');

    function syncAddScale(val) {
        if (addScaleWrap) addScaleWrap.style.display = val === 'rating' ? '' : 'none';
    }
    if (addTypeSelect) {
        addTypeSelect.addEventListener('change', () => syncAddScale(addTypeSelect.value));
        syncAddScale(addTypeSelect.value);
    }

    // ---- Edit modal ----
    const modal      = new bootstrap.Modal(document.getElementById('editQuestionModal'));
    const editForm   = document.getElementById('edit-q-form');
    const editType   = document.getElementById('edit-type');
    const editScale  = document.getElementById('edit-scale-wrap');
    const saveBtn    = document.getElementById('edit-q-save');

    function syncEditScale(val) {
        if (editScale) editScale.style.display = val === 'rating' ? '' : 'none';
    }
    if (editType) {
        editType.addEventListener('change', () => syncEditScale(editType.value));
    }

    document.querySelectorAll('[data-tq-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            editForm.action = `/admin/survey-templates/${templateId}/questions/${d.id}`;
            document.getElementById('edit-text').value     = d.text;
            editType.value                                  = d.type;
            document.getElementById('edit-category').value = d.category || '';
            document.getElementById('edit-scale').value    = d.scale    || '';
            document.getElementById('edit-order').value    = d.order;
            syncEditScale(d.type);
            modal.show();
        });
    });

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            editForm.submit();
        });
    }

    // ---- Drag-to-reorder ----
    const tbody = document.getElementById('tq-sortable');
    if (!tbody) return;

    let dragged = null;

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
            const cell = row.querySelector('.tq-order');
            if (cell) cell.textContent = i + 1;
        });
    }

    function saveOrder() {
        const order = [...tbody.querySelectorAll('tr')].map(function (r, i) {
            return { id: parseInt(r.dataset.id), order_number: i + 1 };
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
@endpush
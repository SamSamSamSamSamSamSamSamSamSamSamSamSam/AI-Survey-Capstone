{{--
    ============================================================
    _weight_config.blade.php
    Reusable weight configuration panel.

    Variables expected:
      $weightQuestions  — collection of rating questions with category info
      $weightFormAction — POST route string for saving weights
      $weightCsrf       — true (just use @csrf in the parent)
      $weightReadOnly   — bool, true = display only (active survey)
      $weightOwner      — 'template' | 'survey'
      $weightOwnerId    — the template ID or survey ID
    ============================================================
--}}

@php
    use Illuminate\Support\Collection;

    // Group rating questions by category and get one weight per category
    $ratingByCategory = $weightQuestions
        ->filter(fn($q) => ($q->question_type ?? $q->questionType ?? '') === 'rating'
                         && !empty($q->category_id ?? $q->categoryId ?? null))
        ->groupBy(fn($q) => $q->category?->name ?? $q->category_name ?? 'Uncategorised');

    $totalWeight = $ratingByCategory->map(fn($qs) =>
        (float)($qs->first()->category_weight ?? 0)
    )->sum();

    $totalWeight = round($totalWeight, 2);
    $isBalanced  = $totalWeight === 100.0;
    $isEmpty     = $ratingByCategory->isEmpty();
@endphp

<div class="card mb-4" id="weight-config-panel">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <span class="fw-600" style="font-size:.875rem;">
                {{-- <i class="bi bi-sliders me-2 text-primary"></i> --}}
                Category Weights
            </span>
            <span class="ms-2 text-muted" style="font-size:.75rem;">
                Rating questions only · Must total 100%
            </span>
        </div>
        @if (!$weightReadOnly && !$isEmpty)
        <div class="d-flex align-items-center gap-2">
            <span id="weight-total-badge"
                  class="badge {{ $isBalanced ? 'bg-success' : 'bg-danger' }}"
                  style="font-size:.75rem;">
                Total: {{ $totalWeight }}%
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="redistributeWeights()">
                <i class="bi bi-arrow-repeat me-1"></i>Auto-distribute
            </button>
        </div>
        @endif
    </div>

    <div class="card-body">

        @if ($isEmpty)
            <p class="text-muted mb-0" style="font-size:.85rem;">
                <i class="bi bi-info-circle me-1"></i>
                No rating questions with categories found.
                Add rating questions with categories assigned to configure weights.
            </p>
        @else

        @if (!$weightReadOnly)
        <form id="weight-form"
              action="{{ $weightFormAction }}"
              method="POST">
            @csrf
            @method('PATCH')
        @endif

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="weight-table">
                    <thead>
                        <tr>
                            <th style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">Category</th>
                            <th style="font-size:.75rem;text-transform:uppercase;color:#6b7280;width:80px;">Questions</th>
                            <th style="font-size:.75rem;text-transform:uppercase;color:#6b7280;width:160px;">Weight (%)</th>
                            <th style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ratingByCategory as $categoryName => $questions)
                        @php
                            $weight   = (float)($questions->first()->category_weight ?? 0);
                            $catId    = $questions->first()->category_id
                                     ?? $questions->first()->categoryId
                                     ?? null;
                        @endphp
                        <tr>
                            <td style="font-size:.875rem;font-weight:500;">
                                {{ $categoryName }}
                                <input type="hidden" name="categories[]" value="{{ $catId }}">
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"
                                      style="font-size:.75rem;">
                                    {{ $questions->count() }}
                                </span>
                            </td>
                            <td>
                                @if ($weightReadOnly)
                                    <span class="fw-600 text-primary"
                                          style="font-size:.9rem;">
                                        {{ number_format($weight, 2) }}%
                                    </span>
                                @else
                                    <div class="input-group input-group-sm" style="width:130px;">
                                        <input type="number"
                                               name="weights[]"
                                               class="form-control weight-input"
                                               value="{{ number_format($weight, 2, '.', '') }}"
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               style="font-size:.875rem;"
                                               data-category="{{ $categoryName }}"
                                               oninput="updateWeightTotal()">
                                        <span class="input-group-text" style="font-size:.8rem;">%</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{-- Progress bar showing proportion --}}
                                <div style="width:100%;max-width:200px;">
                                    <div style="height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                        <div class="weight-bar"
                                             data-weight="{{ $weight }}"
                                             style="height:100%;border-radius:3px;background:#1e3a5f;
                                                    width:{{ $weight }}%;transition:width .2s;">
                                        </div>
                                    </div>
                                    <span class="weight-bar-label"
                                          style="font-size:.7rem;color:#9ca3af;">
                                        {{ number_format($weight, 2) }}% of total
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if (!$weightReadOnly)
                    <tfoot>
                        <tr style="border-top:2px solid #e5e7eb;">
                            <td colspan="2"
                                style="font-size:.875rem;font-weight:600;color:#374151;">
                                Total
                            </td>
                            <td>
                                <span id="weight-total-display"
                                      class="{{ $isBalanced ? 'text-success' : 'text-danger' }} fw-600"
                                      style="font-size:.9rem;">
                                    {{ $totalWeight }}%
                                </span>
                                <div id="weight-error-msg"
                                     class="text-danger mt-1"
                                     style="font-size:.75rem;display:{{ $isBalanced ? 'none' : 'block' }};">
                                    Weights must total exactly 100%
                                </div>
                            </td>
                            <td>
                                <button type="submit"
                                        id="save-weights-btn"
                                        class="btn btn-sm btn-primary"
                                        {{ $isBalanced ? '' : 'disabled' }}>
                                    <i class="bi bi-check-lg me-1"></i>Save Weights
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if (!$weightReadOnly && !$isBalanced)
            <div class="alert alert-warning mt-3 py-2 px-3" style="font-size:.8rem;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Weights currently total <strong>{{ $totalWeight }}%</strong>.
                They must total exactly <strong>100%</strong> before saving.
                Click <strong>Auto-distribute</strong> to reset to equal distribution.
            </div>
            @endif

        @if (!$weightReadOnly)
        </form>
        @endif

        @endif {{-- end not empty --}}

    </div>
</div>

@if (!$weightReadOnly && !$isEmpty)
<script>
(function () {
    function updateWeightTotal() {
        const inputs  = document.querySelectorAll('.weight-input');
        const bars    = document.querySelectorAll('.weight-bar');
        const labels  = document.querySelectorAll('.weight-bar-label');
        let total = 0;

        inputs.forEach(function (inp) { total += parseFloat(inp.value) || 0; });
        total = Math.round(total * 100) / 100;

        const balanced = total === 100;
        const display  = document.getElementById('weight-total-display');
        const badge    = document.getElementById('weight-total-badge');
        const errMsg   = document.getElementById('weight-error-msg');
        const saveBtn  = document.getElementById('save-weights-btn');

        if (display) {
            display.textContent = total + '%';
            display.className = (balanced ? 'text-success' : 'text-danger') + ' fw-600';
        }
        if (badge) {
            badge.textContent = 'Total: ' + total + '%';
            badge.className   = 'badge ' + (balanced ? 'bg-success' : 'bg-danger');
        }
        if (errMsg)  errMsg.style.display  = balanced ? 'none' : 'block';
        if (saveBtn) saveBtn.disabled      = !balanced;

        // Update bars
        inputs.forEach(function (inp, i) {
            const w = parseFloat(inp.value) || 0;
            if (bars[i])  bars[i].style.width     = w + '%';
            if (labels[i]) labels[i].textContent   = w.toFixed(2) + '% of total';
        });
    }

    window.updateWeightTotal = updateWeightTotal;

    window.redistributeWeights = function () {
        const inputs = document.querySelectorAll('.weight-input');
        const count  = inputs.length;
        if (count === 0) return;

        const base      = Math.floor((100 / count) * 100) / 100;
        let   remainder = Math.round((100 - base * count) * 100) / 100;

        inputs.forEach(function (inp, i) {
            inp.value = (i === count - 1)
                ? (base + remainder).toFixed(2)
                : base.toFixed(2);
        });

        updateWeightTotal();
    };

    // Block form submit if not 100%
    const form = document.getElementById('weight-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const inputs = document.querySelectorAll('.weight-input');
            let total = 0;
            inputs.forEach(function (inp) { total += parseFloat(inp.value) || 0; });
            total = Math.round(total * 100) / 100;
            if (total !== 100) {
                e.preventDefault();
                alert('Weights must total exactly 100%. Current total: ' + total + '%');
            }
        });
    }
})();
</script>
@endif

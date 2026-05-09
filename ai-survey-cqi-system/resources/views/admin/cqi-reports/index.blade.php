@extends('layouts.app')
@section('title', 'CQI Reports')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-heading">Continues Quality Improvement Reports</h2>
        <p class="page-subheading">AI-generated Continuous Quality Improvement reports.</p>
    </div>
</div>

{{-- ===== CQI GENERATION STATUS BANNER ===== --}}
{{-- Shown when a job was just dispatched (pending_survey_id in session) --}}
@if (session('cqi_pending_survey_id'))
<div id="cqi-status-banner" class="alert mb-3 d-flex align-items-start gap-3"
     style="border-radius:10px; border:1px solid #d0e8ff; background:#f0f7ff;"
     data-survey-id="{{ session('cqi_pending_survey_id') }}"
     data-sse-url="{{ route('admin.cqi-reports.sse', session('cqi_pending_survey_id')) }}">

    {{-- Spinner / icon --}}
    <div id="cqi-banner-icon" class="flex-shrink-0 mt-1">
        <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
    </div>

    {{-- Text --}}
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
            <strong id="cqi-banner-title">Generating CQI Report…</strong>
            <span id="cqi-banner-badge" class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;">Processing</span>
        </div>
        <p id="cqi-banner-message" class="mb-1 small text-muted">Report is queued, waiting for a worker…</p>
        <p id="cqi-banner-detail" class="mb-0 small text-danger fw-500 d-none"></p>
    </div>

    {{-- Action buttons (hidden until terminal state) --}}
    <div id="cqi-banner-actions" class="d-none flex-shrink-0 d-flex gap-2 align-items-center">
        <button onclick="document.getElementById('cqi-status-banner').remove()" class="btn btn-sm btn-outline-secondary">
            Dismiss
        </button>
        <a id="cqi-banner-view-btn" href="{{ route('admin.cqi-reports.index') }}"
           class="btn btn-sm btn-primary d-none">
            <i class="bi bi-eye me-1"></i> View Report
        </a>
    </div>
</div>
@endif

{{-- Standard flash error (e.g. "No analytics yet") --}}
@if (session('error'))
<div class="alert alert-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
</div>
@endif

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.cqi-reports.index') }}" id="cqi-filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select filter-select">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                                {{ $sem->full_label }}{{ $sem->is_active ? ' · Active' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" id="cqi-search" class="form-control auth-input"
                               placeholder="Search title…" value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select filter-select">
                        <option value="">Active</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all" @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-sliders"></i> Filter</button>
                    <a href="{{ route('admin.cqi-reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" id="cqi-table-wrapper">
    @include('admin.cqi-reports._table')
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================================================
    // 1. Table filter + live search (unchanged)
    // =========================================================================
    const filterForm      = document.getElementById('cqi-filter-form');
    const tableContainer  = document.getElementById('cqi-table-wrapper');
    const searchInput     = document.getElementById('cqi-search');
    const filterSelects   = document.querySelectorAll('.filter-select');
    let debounceTimer;

    const performSearch = () => {
        const formData = new URLSearchParams(new FormData(filterForm)).toString();
        tableContainer.style.opacity = '0.6';
        fetch(`${filterForm.action}?${formData}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
        });
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 300);
    });
    filterSelects.forEach(s => s.addEventListener('change', performSearch));
    filterForm.addEventListener('submit', e => { e.preventDefault(); performSearch(); });

    // =========================================================================
    // 2. SSE status banner
    // =========================================================================
    const banner = document.getElementById('cqi-status-banner');
    if (! banner) return;

    const surveyId  = banner.dataset.surveyId;
    const sseUrl    = banner.dataset.sseUrl;

    const iconEl    = document.getElementById('cqi-banner-icon');
    const titleEl   = document.getElementById('cqi-banner-title');
    const badgeEl   = document.getElementById('cqi-banner-badge');
    const msgEl     = document.getElementById('cqi-banner-message');
    const detailEl  = document.getElementById('cqi-banner-detail');
    const actionsEl = document.getElementById('cqi-banner-actions');
    const viewBtn   = document.getElementById('cqi-banner-view-btn');

    const evtSource = new EventSource(sseUrl);

    evtSource.onmessage = function (event) {
        let data;
        try { data = JSON.parse(event.data); } catch { return; }

        // Update message
        msgEl.textContent = data.message ?? 'Processing…';

        if (data.status === 'completed') {
            evtSource.close();

            // Green success state
            banner.style.background    = '#f0fdf4';
            banner.style.borderColor   = '#bbf7d0';
            iconEl.innerHTML           = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
            titleEl.textContent        = 'CQI Report Generated Successfully';
            badgeEl.textContent        = 'Completed';
            badgeEl.className          = 'badge bg-success bg-opacity-10 text-success';
            msgEl.textContent          = data.report_title ?? 'Your report is ready.';
            actionsEl.classList.remove('d-none');
            actionsEl.classList.add('d-flex');
            viewBtn.classList.remove('d-none');

            // Reload table to show new report
            performSearch();
        }

        if (data.status === 'failed') {
            evtSource.close();

            // Red error state
            banner.style.background    = '#fff5f5';
            banner.style.borderColor   = '#fecaca';
            iconEl.innerHTML           = '<i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>';
            titleEl.textContent        = 'CQI Report Generation Failed';
            badgeEl.textContent        = 'Failed';
            badgeEl.className          = 'badge bg-danger bg-opacity-10 text-danger';

            // Show technical detail for admin
            if (data.raw_error) {
                detailEl.textContent = 'Technical detail: ' + data.raw_error;
                detailEl.classList.remove('d-none');
            }

            actionsEl.classList.remove('d-none');
            actionsEl.classList.add('d-flex');
        }
    };

    evtSource.onerror = function () {
        // Only treat as error if we haven't already reached a terminal state
        if (badgeEl.textContent === 'Processing' || badgeEl.textContent === '') {
            evtSource.close();
            banner.style.background   = '#fffbeb';
            banner.style.borderColor  = '#fde68a';
            iconEl.innerHTML          = '<i class="bi bi-wifi-off text-warning fs-5"></i>';
            titleEl.textContent       = 'Connection Lost';
            badgeEl.textContent       = 'Unknown';
            badgeEl.className         = 'badge bg-warning bg-opacity-10 text-warning';
            msgEl.textContent         = 'Lost connection to the status stream. The report may still be generating — refresh the page in a moment.';
            actionsEl.classList.remove('d-none');
            actionsEl.classList.add('d-flex');
        }
    };
});
</script>
@endpush
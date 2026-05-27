@extends('layouts.app')
@section('title', 'Import Users')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Import</li>
</ol>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"> Upload Error:</i>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h2 class="page-heading">Batch Account Creation</h2>
        <p class="page-subheading text-muted">Upload a CSV file to register multiple members and trigger automated email invitations.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Upload CSV File</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- The form always points to the real import route; JS overrides on preview --}}
                <form action="{{ route('admin.users.import.post') }}" method="POST"
                      enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold">Select File</label>

                        <div class="upload-box @error('csv_file') is-invalid-box @enderror">
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                                   class="upload-input @error('csv_file') is-invalid @enderror">

                            <label for="csv_file" class="upload-label">
                                <div class="upload-icon mb-2" id="uploadIcon">
                                    <i class="bi bi-file-earmark-spreadsheet text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div class="upload-text" id="uploadText">
                                    <span class="fw-bold">Click to upload</span> or drag and drop
                                    <p class="text-muted small mb-0">CSV files only (Max: 2MB)</p>
                                </div>
                                <div id="file-name-display" class="mt-2 text-primary fw-semibold small d-none">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    <span id="file-name"></span>
                                </div>
                            </label>
                        </div>

                        @error('csv_file')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Validation result box (hidden until AJAX returns) ── --}}
                    <div id="validationSection" class="d-none mb-3">
                        <div id="validationBox" class="wizard-validation-box"></div>
                    </div>

                    {{-- ── Action buttons ── --}}
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        {{-- Shown after successful validation --}}
                        <button type="button" id="btnImport"
                                class="btn btn-primary d-none">
                            <span class="btn-text">
                                <i class="bi bi-database-add me-2"></i>
                                Confirm &amp; Import — Send Verification Emails
                            </span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Importing…
                            </span>
                        </button>

                        {{-- Shown after any validation (success or fail) --}}
                        <button type="button" id="btnRetry"
                                class="btn btn-outline-secondary d-none">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Choose Another File
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-info bg-light">
            <div class="card-body">
                <h5 class="card-title text-info d-flex align-items-center">
                    <i class="bi bi-info-circle me-2"></i> CSV Format Guide
                </h5>
                <p class="small text-muted">Ensure your CSV follows this structure (including a header row):</p>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white text-xs">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>Sample</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-monospace fw-bold">user_id_number</td>
                                <td class="text-muted">e.g., 20241001 (8 digits)</td>
                            </tr>
                            <tr>
                                <td class="font-monospace fw-bold">name</td>
                                <td class="text-muted">Full Name (e.g., Juan Dela Cruz)</td>
                            </tr>
                            <tr>
                                <td class="font-monospace fw-bold">email</td>
                                <td class="text-muted">Must be a valid institutional email (e.g., email@usc.edu.ph)</td>
                            </tr>
                            <tr>
                                <td class="font-monospace fw-bold">role</td>
                                <td class="text-muted">Use: <code class="text-primary">faculty</code> or <code class="text-success">student</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning py-2 mt-3 mb-0">
                    <p class="small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        An invitation link will be sent to each email via <strong>Gmail</strong> to set their account password.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading overlay (reuses the same class as the wizard) --}}
<div class="wizard-loading-overlay d-none" id="importLoader">
    <div class="wizard-loading-overlay__inner">
        <div class="spinner-border text-primary mb-3" style="width:2.5rem;height:2.5rem;border-width:3px;"></div>
        <p class="wizard-loading-overlay__text" id="loaderText">Validating CSV…</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const PREVIEW_URL  = "{{ route('admin.users.import.preview') }}";
    const IMPORT_URL   = "{{ route('admin.users.import.post') }}";
    const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── DOM refs ──────────────────────────────────────────────
    const form             = document.getElementById('importForm');
    const fileInput        = document.getElementById('csv_file');
    const uploadIcon       = document.getElementById('uploadIcon');
    const uploadText       = document.getElementById('uploadText');
    const fileNameDisplay  = document.getElementById('file-name-display');
    const fileNameSpan     = document.getElementById('file-name');
    const validationSection= document.getElementById('validationSection');
    const validationBox    = document.getElementById('validationBox');
    const btnImport        = document.getElementById('btnImport');
    const btnRetry         = document.getElementById('btnRetry');
    const loader           = document.getElementById('importLoader');
    const loaderText       = document.getElementById('loaderText');

    // ── Loader helpers ────────────────────────────────────────
    function showLoader(text) {
        if (loaderText) loaderText.textContent = text || 'Processing…';
        loader?.classList.remove('d-none');
    }

    function hideLoader() {
        loader?.classList.add('d-none');
    }

    // ── Show selected filename in the upload box ──────────────
    function markFileSelected(filename) {
        if (fileNameSpan)   fileNameSpan.textContent = filename;
        fileNameDisplay?.classList.remove('d-none');
        uploadText?.classList.add('d-none');
        uploadIcon?.classList.add('d-none');
    }

    // ── Reset everything back to initial state ────────────────
    function resetUpload() {
        fileInput.value = '';
        fileNameDisplay?.classList.add('d-none');
        uploadText?.classList.remove('d-none');
        uploadIcon?.classList.remove('d-none');
        if (fileNameSpan) fileNameSpan.textContent = '';
        validationSection?.classList.add('d-none');
        btnImport?.classList.add('d-none');
        btnRetry?.classList.add('d-none');
        if (validationBox) validationBox.innerHTML = '';
    }

    // ── File chosen → run AJAX preview ───────────────────────
    fileInput?.addEventListener('change', function () {
        if (! this.files[0]) return;
        markFileSelected(this.files[0].name);
        runPreview();
    });

    // ── AJAX preview call ─────────────────────────────────────
    async function runPreview() {
        showLoader('Validating CSV…');
        validationSection?.classList.remove('d-none');
        if (validationBox) validationBox.innerHTML = '';
        btnImport?.classList.add('d-none');
        btnRetry?.classList.add('d-none');

        const fd = new FormData(form);

        // Override the action so this POST goes to the preview route
        fd.set('_method', 'POST');

        try {
            const res  = await fetch(PREVIEW_URL, {
                method:  'POST',
                body:    fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     CSRF_TOKEN,
                },
            });

            // Laravel may return 422 for validation errors; still parse JSON
            const data = await res.json();
            hideLoader();
            renderValidation(data);
        } catch (err) {
            hideLoader();
            if (validationBox) {
                validationBox.innerHTML = buildErrorBox(
                    'Network error',
                    ['Could not reach the server. Please check your connection and try again.']
                );
            }
            btnRetry?.classList.remove('d-none');
        }
    }

    // ── Render validation summary (mirrors wizard-upload.js) ──
    function renderValidation(data) {
        const canProceed = data.can_proceed && (data.valid_count ?? 0) > 0;

        let html = `<div class="wizard-validation-result ${canProceed ? 'wizard-validation-result--ok' : 'wizard-validation-result--fail'}">`;

        // Summary row
        html += `<div class="wizard-validation-result__summary">`;
        html += `<span class="wizard-validation-result__stat">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>${data.valid_count ?? 0}</strong> valid row(s)
                 </span>`;
        if ((data.skipped_count ?? 0) > 0) {
            html += `<span class="wizard-validation-result__stat wizard-validation-result__stat--warn">
                        <i class="bi bi-skip-forward-fill"></i>
                        <strong>${data.skipped_count}</strong> skipped
                     </span>`;
        }
        html += `</div>`;

        // Errors
        if (data.errors?.length) {
            html += `<div class="wizard-validation-result__list wizard-validation-result__list--errors">
                        <p class="wizard-validation-result__list-title">
                            <i class="bi bi-x-circle-fill me-1"></i>Errors (${data.errors.length})
                        </p>
                        <ul>`;
            data.errors.forEach(e => {
                const lineTag = e.line > 0 ? `<span class="wizard-validation-result__line">Line ${e.line}</span> ` : '';
                html += `<li>${lineTag}${escHtml(e.message)}</li>`;
            });
            html += `</ul></div>`;
        }

        // Warnings
        if (data.warnings?.length) {
            html += `<div class="wizard-validation-result__list wizard-validation-result__list--warnings">
                        <p class="wizard-validation-result__list-title">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Warnings (${data.warnings.length})
                        </p>
                        <ul>`;
            data.warnings.forEach(w => {
                const lineTag = w.line > 0 ? `<span class="wizard-validation-result__line">Line ${w.line}</span> ` : '';
                html += `<li>${lineTag}${escHtml(w.message)}</li>`;
            });
            html += `</ul></div>`;
        }

        html += `</div>`;
        if (validationBox) validationBox.innerHTML = html;

        if (canProceed) {
            btnImport?.classList.remove('d-none');
        }
        btnRetry?.classList.remove('d-none');
    }

    function buildErrorBox(title, messages) {
        let html = `<div class="wizard-validation-result wizard-validation-result--fail">
                        <div class="wizard-validation-result__summary">
                            <span class="wizard-validation-result__stat">
                                <i class="bi bi-x-circle-fill"></i> ${escHtml(title)}
                            </span>
                        </div>
                        <div class="wizard-validation-result__list wizard-validation-result__list--errors"><ul>`;
        messages.forEach(m => { html += `<li>${escHtml(m)}</li>`; });
        html += `</ul></div></div>`;
        return html;
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Confirm & Import ─────────────────────────────────────
    btnImport?.addEventListener('click', function () {
        const btnText    = this.querySelector('.btn-text');
        const btnLoading = this.querySelector('.btn-loading');
        this.disabled = true;
        btnText?.classList.add('d-none');
        btnLoading?.classList.remove('d-none');

        showLoader('Importing users & sending emails…');

        form.action = IMPORT_URL;
        form.submit();
    });

    // ── Retry / choose another file ───────────────────────────
    btnRetry?.addEventListener('click', function () {
        resetUpload();
    });

})();
</script>
@endpush
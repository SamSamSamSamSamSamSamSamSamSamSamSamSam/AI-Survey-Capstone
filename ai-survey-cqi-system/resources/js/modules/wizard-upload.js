/**
 * wizard-upload.js
 * Handles the AJAX CSV preview/validation flow for the semester setup wizard.
 * Config is injected by the Blade view via window.WIZARD_CONFIG.
 */

(function () {
    'use strict';

    const cfg = window.WIZARD_CONFIG || {};

    // ── DOM refs ──────────────────────────────────────────────
    const form             = document.getElementById('wizardUploadForm');
    const fileInput        = document.getElementById('file-' + cfg.step);
    const zone             = document.getElementById('uploadZone-' + cfg.step);
    const zoneIdle         = document.getElementById('zoneIdle-' + cfg.step);
    const zoneSelected     = document.getElementById('zoneSelected-' + cfg.step);
    const zoneName         = document.getElementById('zoneName-' + cfg.step);
    const validationSection= document.getElementById('validationSection');
    const validationBox    = document.getElementById('validationBox');
    const btnImport        = document.getElementById('btnImport');
    const btnRetry         = document.getElementById('btnRetry');
    const loader           = document.getElementById('wizardLoader');
    const loaderText       = document.getElementById('loaderText');

    if (! fileInput || ! form) return;

    // ── Helpers ───────────────────────────────────────────────
    function showLoader(text) {
        if (loaderText) loaderText.textContent = text || 'Processing…';
        loader?.classList.remove('d-none');
    }

    function hideLoader() {
        loader?.classList.add('d-none');
    }

    function setZoneSelected(filename) {
        zone?.classList.add('upload-zone--selected');
        zoneIdle?.classList.add('d-none');
        zoneSelected?.classList.remove('d-none');
        if (zoneName) zoneName.textContent = filename;
    }

    // ── Reset (retry) ─────────────────────────────────────────
    window.resetWizardUpload = function () {
        fileInput.value = '';
        zone?.classList.remove('upload-zone--selected');
        zoneIdle?.classList.remove('d-none');
        zoneSelected?.classList.add('d-none');
        if (zoneName) zoneName.textContent = '—';
        validationSection?.classList.add('d-none');
        btnImport?.classList.add('d-none');
        btnRetry?.classList.add('d-none');
        if (validationBox) validationBox.innerHTML = '';
    };

    // ── File selection → AJAX preview ────────────────────────
    fileInput?.addEventListener('change', function () {
        if (! this.files[0]) return;
        setZoneSelected(this.files[0].name);
        runPreview();
    });

    // ── Drag & drop on the zone ───────────────────────────────
    zone?.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('upload-zone--drag');
    });
    zone?.addEventListener('dragleave', function () {
        this.classList.remove('upload-zone--drag');
    });
    zone?.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('upload-zone--drag');
        const file = e.dataTransfer?.files[0];
        if (! file) return;
        // Manually assign to input
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        setZoneSelected(file.name);
        runPreview();
    });

    // ── AJAX preview ─────────────────────────────────────────
    async function runPreview() {
        showLoader('Validating CSV…');
        validationSection?.classList.remove('d-none');
        if (validationBox) validationBox.innerHTML = '';
        btnImport?.classList.add('d-none');
        btnRetry?.classList.add('d-none');

        const fd = new FormData(form);

        try {
            const res  = await fetch(cfg.previewUrl, {
                method:  'POST',
                body:    fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
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

    // ── Render validation results ────────────────────────────
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
                        <strong>${data.skipped_count}</strong> skipped (duplicates)
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
                html += `<li><span class="wizard-validation-result__line">Line ${e.line}</span> ${escHtml(e.message)}</li>`;
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
                html += `<li><span class="wizard-validation-result__line">Line ${w.line}</span> ${escHtml(w.message)}</li>`;
            });
            html += `</ul></div>`;
        }

        html += `</div>`;
        if (validationBox) validationBox.innerHTML = html;

        // Show/hide action buttons
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
        const route = cfg.importRoutes?.[cfg.step];
        if (! route) return;

        // Swap button to loading state
        const btnText    = this.querySelector('.btn-text');
        const btnLoading = this.querySelector('.btn-loading');
        this.disabled = true;
        btnText?.classList.add('d-none');
        btnLoading?.classList.remove('d-none');

        showLoader('Importing data to database…');

        form.action = route;
        form.submit();
    });

})();
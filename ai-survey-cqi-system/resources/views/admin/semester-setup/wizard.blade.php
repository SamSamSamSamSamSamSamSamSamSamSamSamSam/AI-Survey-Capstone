@extends('admin.layouts.app')
@section('title', 'Semester Setup Wizard')

@section('content')

<style>
    .wizard-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; align-items: start; }

    /* Progress rail */
    .progress-rail { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; position: sticky; top: 1.5rem; }
    .progress-rail-header { background: #1e1b4b; color: #fff; padding: 1rem 1.25rem; font-size: .9rem; font-weight: 600; }
    .progress-rail-header small { display: block; font-size: .75rem; color: #a5b4fc; font-weight: 400; margin-top: .2rem; }
    .step-item { display: flex; align-items: center; gap: .85rem; padding: .85rem 1.25rem; border-bottom: 1px solid #f3f4f6; text-decoration: none; transition: background .15s; }
    .step-item:last-child { border-bottom: none; }
    .step-item:hover { background: #f9fafb; }
    .step-item.active { background: #eef2ff; border-left: 3px solid #4f46e5; }
    .step-item.done   { background: #f0fdf4; }
    .step-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; }
    .step-dot.pending { background: #f3f4f6; color: #9ca3af; border: 2px solid #e5e7eb; }
    .step-dot.active  { background: #4f46e5; color: #fff; }
    .step-dot.done    { background: #059669; color: #fff; }
    .step-label { font-size: .875rem; color: #374151; font-weight: 500; }
    .step-count { font-size: .75rem; color: #6b7280; }

    /* Step content */
    .step-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
    .step-card-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: .75rem; }
    .step-card-header .step-number { background: #4f46e5; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; }
    .step-card-header h2 { font-size: 1.05rem; font-weight: 600; }
    .step-card-body { padding: 1.5rem; }

    /* CSV format box */
    .csv-format { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; font-size: .82rem; }
    .csv-format h4 { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: .5rem; }
    .csv-format code { background: #e2e8f0; padding: .2rem .45rem; border-radius: 4px; font-size: .8rem; color: #1e293b; }
    .csv-format table { width: 100%; border-collapse: collapse; margin-top: .5rem; }
    .csv-format th { padding: .3rem .5rem; background: #e2e8f0; font-size: .75rem; color: #475569; text-align: left; }
    .csv-format td { padding: .3rem .5rem; font-size: .78rem; color: #374151; border-bottom: 1px solid #f1f5f9; }

    /* Upload box */
    .upload-area { position: relative; }
    .upload-box { border: 2px dashed #d1d5db; border-radius: 8px; padding: 1.5rem; text-align: center; transition: all .15s; cursor: pointer; }
    .upload-box:hover { border-color: #6366f1; background: #f8f9ff; }
    .upload-box.has-file { border-color: #059669; background: #f0fdf4; }
    .upload-box .icon { font-size: 2rem; margin-bottom: .5rem; }
    .upload-box p { font-size: .875rem; color: #6b7280; }
    .upload-box .file-name { font-size: .82rem; color: #4f46e5; font-weight: 500; margin-top: .35rem; min-height: 1.2em; }
    .upload-box.has-file .file-name { color: #059669; }

    /* Upload button */
    .btn-upload { padding: .6rem 1.5rem; background: #4f46e5; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: .5rem; transition: background .15s; }
    .btn-upload:hover:not(:disabled) { background: #4338ca; }
    .btn-upload:disabled { background: #a5b4fc; cursor: not-allowed; }
    .btn-skip { font-size: .85rem; color: #6b7280; background: none; border: none; cursor: pointer; margin-left: 1rem; text-decoration: underline; }

    /* Loading overlay */
    .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; flex-direction: column; gap: 1rem; }
    .loading-overlay.visible { display: flex; }
    .spinner { width: 48px; height: 48px; border: 5px solid #fff3; border-top-color: #fff; border-radius: 50%; animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading-text { color: #fff; font-size: 1rem; font-weight: 600; }
    .loading-sub  { color: #ffffffcc; font-size: .85rem; }

    /* Progress bar inside loading */
    .upload-progress-bar { width: 260px; height: 6px; background: #ffffff33; border-radius: 999px; overflow: hidden; }
    .upload-progress-fill { height: 100%; background: #fff; border-radius: 999px; transition: width .2s; width: 0%; }

    /* Error / success */
    .alert { padding: .75rem 1rem; border-radius: 7px; font-size: .875rem; margin-bottom: 1.25rem; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .error-list { background: #fef2f2; border: 1px solid #fecaca; border-radius: 7px; padding: .75rem 1rem; margin-bottom: 1rem; max-height: 200px; overflow-y: auto; }
    .error-list strong { font-size: .82rem; color: #b91c1c; }
    .error-list p { font-size: .8rem; color: #b91c1c; margin-top: .2rem; }

    /* PHP config warning */
    .config-warn { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 7px; padding: .75rem 1rem; margin-bottom: 1.25rem; font-size: .82rem; color: #92400e; }
    .config-warn code { background: #fef3c7; padding: .1rem .35rem; border-radius: 3px; font-size: .8rem; }
</style>

{{-- Loading overlay --}}
<div class="loading-overlay" id="loading-overlay">
    <div class="spinner"></div>
    <div class="loading-text" id="loading-text">Uploading file…</div>
    <div class="upload-progress-bar">
        <div class="upload-progress-fill" id="upload-progress-fill"></div>
    </div>
    <div class="loading-sub" id="loading-sub">Please do not close this page.</div>
</div>

{{-- PHP config check --}}
@php
    $uploadMax   = ini_get('upload_max_filesize');
    $postMax     = ini_get('post_max_size');
    $execTime    = ini_get('max_execution_time');
    $configOk    = (int) $uploadMax >= 10 && (int) $execTime >= 60;
@endphp

@if (! $configOk)
<div class="config-warn">
    ⚠ <strong>PHP configuration may be too restrictive for large CSV files.</strong>
    Current: <code>upload_max_filesize={{ $uploadMax }}</code>,
    <code>post_max_size={{ $postMax }}</code>,
    <code>max_execution_time={{ $execTime }}s</code>.
    In Laragon: open <strong>Menu → PHP → php.ini</strong> and set
    <code>upload_max_filesize=20M</code>, <code>post_max_size=25M</code>,
    <code>max_execution_time=120</code>, then restart.
</div>
@endif

{{-- Active semester banner --}}
<div class="alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;margin-bottom:1.25rem;">
    <strong>Active Semester:</strong> {{ $activeSemester->full_label }}
</div>

<div class="wizard-wrap">

    {{-- ── Left: Progress Rail ── --}}
    <div class="progress-rail">
        <div class="progress-rail-header">
            Semester Setup
            <small>{{ $activeSemester->full_label }}</small>
        </div>

        @foreach ($steps as $num => $step)
        @php
            $isDone   = $stepStats[$num] > 0;
            $isActive = $currentStep === $num;
            $cls      = $isActive ? 'active' : ($isDone ? 'done' : '');
            $dotCls   = $isActive ? 'active' : ($isDone ? 'done' : 'pending');
        @endphp
        <a href="{{ route('admin.semester-setup.index', ['step' => $num]) }}"
           class="step-item {{ $cls }}">
            <div class="step-dot {{ $dotCls }}">
                {{ $isDone && ! $isActive ? '✓' : $num }}
            </div>
            <div>
                <div class="step-label">{{ $step['icon'] }} {{ $step['label'] }}</div>
                <div class="step-count">{{ number_format($stepStats[$num]) }} record(s)</div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ── Right: Step Content ── --}}
    <div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('import_errors'))
            <div class="error-list">
                <strong>Import Warnings (rows skipped):</strong>
                @foreach (session('import_errors') as $err)
                    <p>{{ $err }}</p>
                @endforeach
            </div>
        @endif

        @if ($errors->any())
            <div class="error-list">
                <strong>Validation Errors:</strong>
                @foreach ($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        {{-- ─────────────────── STEP 1 ─────────────────── --}}
        @if ($currentStep === 1)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">1</div>
                <h2>👤 Register Students</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV to register student accounts. Existing students (matched by ID number) are skipped.
                    Default password is set to the student's ID number.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format — <code>students.csv</code></h4>
                    <table>
                        <tr><th>Column</th><th>Required</th><th>Example</th></tr>
                        <tr><td><code>user_id_number</code></td><td>Yes</td><td>2024-00001</td></tr>
                        <tr><td><code>name</code></td><td>Yes</td><td>Juan dela Cruz</td></tr>
                        <tr><td><code>email</code></td><td>Yes</td><td>juan@school.edu</td></tr>
                    </table>
                    <p style="margin-top:.5rem;color:#6b7280;">
                        First row must be the header. UTF-8 encoding recommended.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('admin.semester-setup.import-students') }}"
                      enctype="multipart/form-data"
                      id="form-step-1"
                      onsubmit="handleUpload(event, this, 'Importing students…')">
                    @csrf
                    @include('admin.semester-setup._upload_field', ['stepId' => 1, 'label' => 'students.csv'])
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" id="btn-step-1" class="btn-upload" disabled>
                            <span>Upload &amp; Continue →</span>
                        </button>
                        <a href="{{ route('admin.semester-setup.index', ['step' => 2]) }}" class="btn-skip">Skip this step</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────── STEP 2 ─────────────────── --}}
        @elseif ($currentStep === 2)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">2</div>
                <h2>🏫 Create Blocks</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV to create blocks for the active semester. Programs must already exist.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format — <code>blocks.csv</code></h4>
                    <table>
                        <tr><th>Column</th><th>Required</th><th>Example</th></tr>
                        <tr><td><code>block_name</code></td><td>Yes</td><td>BSIT-2A</td></tr>
                        <tr><td><code>program_code</code></td><td>Yes</td><td>BSIT</td></tr>
                        <tr><td><code>year_level</code></td><td>Yes</td><td>2</td></tr>
                    </table>
                </div>

                <form method="POST"
                      action="{{ route('admin.semester-setup.import-blocks') }}"
                      enctype="multipart/form-data"
                      id="form-step-2"
                      onsubmit="handleUpload(event, this, 'Creating blocks…')">
                    @csrf
                    @include('admin.semester-setup._upload_field', ['stepId' => 2, 'label' => 'blocks.csv'])
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" id="btn-step-2" class="btn-upload" disabled>
                            <span>Upload &amp; Continue →</span>
                        </button>
                        <a href="{{ route('admin.semester-setup.index', ['step' => 3]) }}" class="btn-skip">Skip this step</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────── STEP 3 ─────────────────── --}}
        @elseif ($currentStep === 3)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">3</div>
                <h2>📚 Import Course Offerings</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV of all course offerings. Subjects and faculty must already exist.
                    <code>block_name</code> and <code>offering_type</code> are optional.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format — <code>offerings.csv</code></h4>
                    <table>
                        <tr><th>Column</th><th>Required</th><th>Example</th></tr>
                        <tr><td><code>subject_code</code></td><td>Yes</td><td>CIS2105</td></tr>
                        <tr><td><code>teacher_id_number</code></td><td>Yes</td><td>2020-00042</td></tr>
                        <tr><td><code>group_number</code></td><td>No</td><td>3</td></tr>
                        <tr><td><code>block_name</code></td><td>No</td><td>BSIT-2A</td></tr>
                        <tr><td><code>offering_type</code></td><td>No</td><td>Regular</td></tr>
                    </table>
                </div>

                <form method="POST"
                      action="{{ route('admin.semester-setup.import-offerings') }}"
                      enctype="multipart/form-data"
                      id="form-step-3"
                      onsubmit="handleUpload(event, this, 'Importing course offerings…')">
                    @csrf
                    @include('admin.semester-setup._upload_field', ['stepId' => 3, 'label' => 'offerings.csv'])
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" id="btn-step-3" class="btn-upload" disabled>
                            <span>Upload &amp; Continue →</span>
                        </button>
                        <a href="{{ route('admin.semester-setup.index', ['step' => 4]) }}" class="btn-skip">Skip this step</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────── STEP 4 ─────────────────── --}}
        @elseif ($currentStep === 4)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">4</div>
                <h2>📋 Import Enrollments</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV of all student enrollments. Students and offerings must already exist.
                    <code>enrollment_type</code> defaults to <em>Block-Enrolled</em> if omitted.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format — <code>enrollments.csv</code></h4>
                    <table>
                        <tr><th>Column</th><th>Required</th><th>Example</th></tr>
                        <tr><td><code>student_id_number</code></td><td>Yes</td><td>2024-00001</td></tr>
                        <tr><td><code>subject_code</code></td><td>Yes</td><td>CIS2105</td></tr>
                        <tr><td><code>group_number</code></td><td>No</td><td>3</td></tr>
                        <tr><td><code>enrollment_type</code></td><td>No</td><td>Block-Enrolled</td></tr>
                    </table>
                </div>

                <form method="POST"
                      action="{{ route('admin.semester-setup.import-enrollments') }}"
                      enctype="multipart/form-data"
                      id="form-step-4"
                      onsubmit="handleUpload(event, this, 'Importing enrollments…')">
                    @csrf
                    @include('admin.semester-setup._upload_field', ['stepId' => 4, 'label' => 'enrollments.csv'])
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" id="btn-step-4" class="btn-upload" disabled>
                            <span>Finish Setup ✓</span>
                        </button>
                    </div>
                </form>

                @if ($stepStats[4] > 0)
                <div style="margin-top:1.5rem;padding:1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
                    <p style="font-weight:600;color:#065f46;margin-bottom:.5rem;">✓ Semester Setup Complete</p>
                    <div style="font-size:.875rem;color:#374151;display:grid;grid-template-columns:1fr 1fr;gap:.35rem;">
                        <span>Students:</span>    <span><strong>{{ number_format($stepStats[1]) }}</strong></span>
                        <span>Blocks:</span>       <span><strong>{{ number_format($stepStats[2]) }}</strong></span>
                        <span>Offerings:</span>    <span><strong>{{ number_format($stepStats[3]) }}</strong></span>
                        <span>Enrollments:</span>  <span><strong>{{ number_format($stepStats[4]) }}</strong></span>
                    </div>
                    <a href="{{ route('admin.surveys.global-assign') }}"
                       style="display:inline-block;margin-top:1rem;padding:.55rem 1.25rem;background:#4f46e5;color:#fff;border-radius:7px;font-size:.875rem;font-weight:600;text-decoration:none;">
                        → Proceed to Global Survey Assignment
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

<script>
/**
 * Handle CSV upload with XHR so we get real progress events.
 * Falls back to normal form submit if XHR fails.
 */
function handleUpload(event, form, loadingMessage) {
    const fileInput = form.querySelector('input[type="file"]');

    if (! fileInput || ! fileInput.files.length) {
        alert('Please select a CSV file first.');
        event.preventDefault();
        return;
    }

    const file = fileInput.files[0];

    // Client-side size guard (10 MB)
    if (file.size > 10 * 1024 * 1024) {
        alert('File is too large (max 10 MB). Please split it into smaller files.');
        event.preventDefault();
        return;
    }

    // Show loading overlay
    const overlay  = document.getElementById('loading-overlay');
    const loadText = document.getElementById('loading-text');
    const loadSub  = document.getElementById('loading-sub');
    const fill     = document.getElementById('upload-progress-fill');

    loadText.textContent = loadingMessage;
    loadSub.textContent  = `Uploading ${file.name} (${(file.size / 1024).toFixed(1)} KB)…`;
    overlay.classList.add('visible');

    // Use XHR for upload progress tracking
    event.preventDefault();

    const formData = new FormData(form);
    const xhr      = new XMLHttpRequest();

    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    // Upload progress
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            fill.style.width = pct + '%';
            loadSub.textContent = `Uploading… ${pct}%`;
        }
    });

    xhr.upload.addEventListener('load', () => {
        fill.style.width = '100%';
        loadText.textContent = 'Processing rows…';
        loadSub.textContent = 'This may take a moment for large files.';
    });

    xhr.addEventListener('load', () => {
        // Redirect on success (3xx) or parse Laravel redirect response
        if (xhr.status >= 200 && xhr.status < 400) {
            // Laravel returns HTML — just redirect to the response URL
            const redirectUrl = xhr.responseURL;
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                window.location.reload();
            }
        } else {
            overlay.classList.remove('visible');
            alert('Upload failed (HTTP ' + xhr.status + '). Check your php.ini settings and try again.');
        }
    });

    xhr.addEventListener('error', () => {
        overlay.classList.remove('visible');
        alert('Network error during upload. Check that your PHP upload limits are set correctly in php.ini.');
    });

    xhr.addEventListener('timeout', () => {
        overlay.classList.remove('visible');
        alert('Upload timed out. Try a smaller file or increase max_execution_time in php.ini.');
    });

    xhr.timeout = 120000; // 2 minutes
    xhr.send(formData);
}

// Enable submit button only when file is selected
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const step     = this.closest('form').id.replace('form-step-', '');
        const btn      = document.getElementById('btn-step-' + step);
        const box      = this.closest('.upload-box');
        const nameEl   = box.querySelector('.file-name');

        if (this.files.length) {
            const file  = this.files[0];
            const sizeKb = (file.size / 1024).toFixed(1);
            nameEl.textContent = `${file.name} (${sizeKb} KB)`;
            box.classList.add('has-file');
            if (btn) btn.disabled = false;
        } else {
            nameEl.textContent = 'No file selected';
            box.classList.remove('has-file');
            if (btn) btn.disabled = true;
        }
    });
});
</script>

@endsection

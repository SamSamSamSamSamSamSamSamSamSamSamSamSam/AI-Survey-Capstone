@extends('admin.layouts.app')
@section('title', 'Semester Setup Wizard')

@section('content')

<style>
    .wizard-wrap { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start; }

    /* Progress rail */
    .progress-rail { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; position: sticky; top: 1.5rem; }
    .progress-rail-header { background: #1e1b4b; color: #fff; padding: 1rem 1.25rem; font-size: .9rem; font-weight: 600; }
    .progress-rail-header small { display: block; font-size: .75rem; color: #a5b4fc; font-weight: 400; margin-top: .2rem; }
    
    .step-item { display: flex; align-items: center; gap: .85rem; padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6; text-decoration: none; transition: background .15s; color: #374151; }
    .step-item:last-child { border-bottom: none; }
    .step-item:hover:not(.disabled) { background: #f9fafb; }
    .step-item.active { background: #eef2ff; border-left: 4px solid #4f46e5; color: #4338ca; font-weight: 600; }
    .step-item.completed { color: #059669; background: #f0fdf4; }
    .step-item.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
    
    .step-label { display: flex; flex-direction: column; line-height: 1.2; }
    .step-label small { font-size: .7rem; color: #6b7280; font-weight: 400; margin-top: 2px; }

    /* Content Area */
    .wizard-card { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .wizard-card h2 { margin-bottom: 0.5rem; font-size: 1.25rem; color: #111827; }
    .wizard-card p.subtitle { color: #6b7280; margin-bottom: 2rem; font-size: 0.95rem; }

    /* CSV format guide table */
    .csv-format { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: .82rem; }
    .csv-format h4 { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: .5rem; }
    .csv-format code { background: #e2e8f0; padding: .2rem .45rem; border-radius: 4px; font-size: .8rem; color: #1e293b; }
    .csv-format table { width: 100%; border-collapse: collapse; margin-top: .5rem; }
    .csv-format th { padding: .3rem .5rem; background: #e2e8f0; font-size: .75rem; color: #475569; text-align: left; }
    .csv-format td { padding: .3rem .5rem; font-size: .78rem; color: #374151; border-bottom: 1px solid #f1f5f9; }

    /* Upload Styling */
    .upload-area { border: 2px dashed #e5e7eb; border-radius: 12px; padding: 3rem 2rem; text-align: center; transition: all 0.2s; cursor: pointer; position: relative; }
    .upload-area:hover { border-color: #4f46e5; background: #fcfcff; }
    .upload-area.has-file { border-color: #059669; background: #f0fdf4; }
    .upload-area .icon { font-size: 2.5rem; margin-bottom: 1rem; }
    .upload-area .file-name { margin-top: 1rem; font-weight: 500; color: #4f46e5; }

    .btn-primary { background: #4f46e5; color: #fff; padding: 0.75rem 1.5rem; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; }
    .btn-primary:disabled { background: #9ca3af; cursor: not-allowed; }

    /* Overlay */
    .loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: none; align-items: center; justify-content: center; z-index: 9999; flex-direction: column; gap: 1rem; }
    .loading-overlay.visible { display: flex; }
    .spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #4f46e5; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .config-warn { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 7px; padding: .75rem 1rem; margin-bottom: 1.25rem; font-size: .82rem; color: #92400e; }
</style>

{{-- PHP config check --}}
@php
    $uploadMax = ini_get('upload_max_filesize');
    $execTime = ini_get('max_execution_time');
    $configOk = (int) $uploadMax >= 10 && (int) $execTime >= 60;
@endphp

@if (!$configOk)
<div class="config-warn">
    ⚠ <strong>PHP configuration might be too low:</strong> upload_max_filesize={{ $uploadMax }}, max_execution_time={{ $execTime }}s. 
    If large CSVs fail, increase these in <code>php.ini</code>.
</div>
@endif

<div class="wizard-wrap">
    <aside class="progress-rail">
        <div class="progress-rail-header">
            Setup Progress
            <small>{{ $activeSemester->semester_name }} {{ $activeSemester->academic_year }}</small>
        </div>
        <nav>
            @foreach($steps as $num => $s)
                @php
                    $isCompleted = ($stats[$num] ?? 0) > 0;
                    $isActive = $num == $step;
                    $isDisabled = $num > $step && !$isCompleted;
                @endphp
                <a href="{{ $isDisabled ? '#' : route('admin.semester-setup.index', ['step' => $num]) }}" 
                   class="step-item {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }} {{ $isDisabled ? 'disabled' : '' }}">
                    <span class="icon">{{ $isCompleted ? '✅' : $s['icon'] }}</span>
                    <div class="step-label">
                        {{ $s['label'] }}
                        <small>{{ number_format($stats[$num] ?? 0) }} Records Found</small>
                    </div>
                </a>
            @endforeach
        </nav>
    </aside>

    <main class="wizard-card">
        <h2>{{ $steps[$step]['label'] }}</h2>
        <p class="subtitle">Follow the format guide and upload your CSV file.</p>

        {{-- Step-Specific CSV Guides --}}
        <div class="csv-format">
            <h4>Required CSV Columns — <code>{{ $steps[$step]['key'] }}.csv</code></h4>
            @if($step == 1)
                <table>
                    <tr><th>Column</th><th>Example</th></tr>
                    <tr><td><code>user_id_number</code></td><td>2021-00123</td></tr>
                    <tr><td><code>name</code></td><td>Juan dela Cruz</td></tr>
                    <tr><td><code>email</code></td><td>juan@usc.edu.ph</td></tr>
                </table>
            @elseif($step == 2)
                <table>
                    <tr><th>Column</th><th>Example</th></tr>
                    <tr><td><code>block_name</code></td><td>BSIT-4A</td></tr>
                    <tr><td><code>program_code</code></td><td>BSIT</td></tr>
                    <tr><td><code>year_level</code></td><td>4</td></tr>
                </table>
            @elseif($step == 3)
                <table>
                    <tr><th>Column</th><th>Example</th></tr>
                    <tr><td><code>subject_code</code></td><td>IT4101</td></tr>
                    <tr><td><code>teacher_id_number</code></td><td>2010-00001</td></tr>
                    <tr><td><code>group_number</code></td><td>1 (Optional)</td></tr>
                </table>
            @elseif($step == 4)
                <table>
                    <tr><th>Column</th><th>Example</th></tr>
                    <tr><td><code>student_id_number</code></td><td>2021-00123</td></tr>
                    <tr><td><code>subject_code</code></td><td>IT4101</td></tr>
                </table>
            @endif
        </div>

        <form id="upload-form" action="{{ route('admin.semester-setup.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="step" value="{{ $step }}">
            
            <div class="upload-area" id="upload-area" onclick="document.getElementById('file-input').click()">
                <div class="icon">📄</div>
                <p>Click or drag & drop <strong>{{ $steps[$step]['key'] }}.csv</strong></p>
                <div class="file-name" id="file-name-display">No file selected</div>
                <input type="file" id="file-input" name="csv_file" accept=".csv" style="display:none;">
            </div>

            <div id="preview-section" style="margin-top: 2rem; display: none;">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;">Validation Results</h3>
                <div id="validation-output"></div>
                
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="button" id="btn-import" class="btn-primary" style="display: none;">
                        Confirm and Import
                    </button>
                    <button type="button" onclick="window.location.reload()" class="btn-secondary" style="background:#f3f4f6; border:none; padding:0.75rem 1.5rem; border-radius:6px; cursor:pointer;">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>

<div class="loading-overlay" id="loader">
    <div class="spinner"></div>
    <p id="loader-text">Processing CSV...</p>
</div>

<script>
    const form = document.getElementById('upload-form');
    const fileInput = document.getElementById('file-input');
    const previewSection = document.getElementById('preview-section');
    const validationOutput = document.getElementById('validation-output');
    const btnImport = document.getElementById('btn-import');
    const loader = document.getElementById('loader');
    const uploadArea = document.getElementById('upload-area');
    const fileNameDisplay = document.getElementById('file-name-display');

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileNameDisplay.innerText = this.files[0].name;
            uploadArea.classList.add('has-file');
            handlePreview();
        }
    });

    async function handlePreview() {
        loader.classList.add('visible');
        const formData = new FormData(form);

        try {
            const response = await fetch("{{ route('admin.semester-setup.preview') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();
            loader.classList.remove('visible');
            
            previewSection.style.display = 'block';
            renderValidation(data);

        } catch (error) {
            loader.classList.remove('visible');
            alert('Error validating file. Please ensure it is a valid CSV.');
        }
    }

    function renderValidation(data) {
        let html = `<div style="padding: 1rem; border-radius: 8px; background: ${data.can_proceed ? '#f0fdf4' : '#fef2f2'}; border: 1px solid ${data.can_proceed ? '#bbf7d0' : '#fecaca'};">`;
        
        html += `<p><strong>Valid Rows:</strong> ${data.valid_count}</p>`;
        if (data.skipped_count > 0) html += `<p style="color: #92400e;"><strong>Skipped (Duplicates):</strong> ${data.skipped_count}</p>`;
        
        if (data.errors && data.errors.length > 0) {
            html += `<ul style="margin-top: 1rem; color: #b91c1c; font-size: 0.85rem; max-height: 200px; overflow-y: auto;">`;
            data.errors.forEach(err => {
                html += `<li>Line ${err.line}: ${err.message}</li>`;
            });
            html += `</ul>`;
        }

        if (data.warnings && data.warnings.length > 0) {
            html += `<ul style="margin-top: 1rem; color: #92400e; font-size: 0.85rem;">`;
            data.warnings.forEach(warn => {
                html += `<li>Line ${warn.line}: ${warn.message}</li>`;
            });
            html += `</ul>`;
        }

        html += `</div>`;
        validationOutput.innerHTML = html;

        if (data.can_proceed && data.valid_count > 0) {
            btnImport.style.display = 'block';
        } else {
            btnImport.style.display = 'none';
        }
    }

    btnImport.addEventListener('click', function() {
        const stepRoutes = {
            1: "{{ route('admin.semester-setup.import-students') }}",
            2: "{{ route('admin.semester-setup.import-blocks') }}",
            3: "{{ route('admin.semester-setup.import-offerings') }}",
            4: "{{ route('admin.semester-setup.import-enrollments') }}"
        };

        form.action = stepRoutes[{{ $step }}];
        document.getElementById('loader-text').innerText = "Importing data to database...";
        loader.classList.add('visible');
        form.submit();
    });
</script>

@endsection
@extends('admin.layouts.app')
@section('title', 'Semester Setup Wizard')

@section('content')

<style>
    /* ── Wizard layout ── */
    .wizard-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; align-items: start; }

    /* ── Progress tracker (left rail) ── */
    .progress-rail { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; position: sticky; top: 1.5rem; }
    .progress-rail-header { background: #1e1b4b; color: #fff; padding: 1rem 1.25rem; font-size: .9rem; font-weight: 600; }
    .progress-rail-header small { display: block; font-size: .75rem; color: #a5b4fc; font-weight: 400; margin-top: .2rem; }
    .step-item { display: flex; align-items: center; gap: .85rem; padding: .85rem 1.25rem; border-bottom: 1px solid #f3f4f6; cursor: pointer; text-decoration: none; transition: background .15s; }
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

    /* ── Step content card ── */
    .step-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
    .step-card-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: .75rem; }
    .step-card-header .step-number { background: #4f46e5; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; }
    .step-card-header h2 { font-size: 1.05rem; font-weight: 600; }
    .step-card-body { padding: 1.5rem; }

    /* ── CSV format box ── */
    .csv-format { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; font-size: .82rem; }
    .csv-format h4 { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: .5rem; }
    .csv-format code { background: #e2e8f0; padding: .2rem .45rem; border-radius: 4px; font-size: .8rem; color: #1e293b; }
    .csv-format table { width: 100%; border-collapse: collapse; margin-top: .5rem; }
    .csv-format th { padding: .3rem .5rem; background: #e2e8f0; font-size: .75rem; color: #475569; text-align: left; }
    .csv-format td { padding: .3rem .5rem; font-size: .78rem; color: #374151; border-bottom: 1px solid #f1f5f9; }

    /* ── Upload box ── */
    .upload-box { border: 2px dashed #d1d5db; border-radius: 8px; padding: 1.5rem; text-align: center; transition: border-color .15s; cursor: pointer; }
    .upload-box:hover { border-color: #6366f1; background: #f8f9ff; }
    .upload-box input[type=file] { display: none; }
    .upload-box .icon { font-size: 2rem; margin-bottom: .5rem; }
    .upload-box p { font-size: .875rem; color: #6b7280; }
    .upload-box .file-name { font-size: .82rem; color: #4f46e5; font-weight: 500; margin-top: .35rem; }

    /* ── Error list ── */
    .error-list { background: #fef2f2; border: 1px solid #fecaca; border-radius: 7px; padding: .75rem 1rem; margin-bottom: 1rem; max-height: 180px; overflow-y: auto; }
    .error-list p { font-size: .8rem; color: #b91c1c; margin-bottom: .2rem; }

    /* Buttons */
    .btn-wizard { padding: .6rem 1.5rem; background: #4f46e5; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; }
    .btn-wizard:hover { background: #4338ca; }
    .btn-skip { font-size: .85rem; color: #6b7280; background: none; border: none; cursor: pointer; margin-left: 1rem; text-decoration: underline; }
</style>

{{-- Semester banner --}}
<div class="alert alert-info" style="margin-bottom:1.25rem;">
    <strong>Active Semester:</strong> {{ $activeSemester->full_label }}
    &nbsp;·&nbsp; Running setup for this semester.
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
        <a href="{{ route('admin.semester-setup.index', ['step' => $num]) }}" class="step-item {{ $cls }}">
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
            <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        @if (session('import_errors'))
            <div class="error-list" style="margin-bottom:1rem;">
                <strong style="font-size:.82rem;color:#b91c1c;">Import Warnings:</strong>
                @foreach (session('import_errors') as $err)
                    <p>{{ $err }}</p>
                @endforeach
            </div>
        @endif

        @if ($errors->any())
            <div class="error-list" style="margin-bottom:1rem;">
                @foreach ($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        {{-- ─────────────────── STEP 1: Students ─────────────────── --}}
        @if ($currentStep === 1)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">1</div>
                <h2>👤 Register Students</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV file to register student accounts for this semester.
                    Existing students (matched by ID number) will be skipped.
                    Default password is set to the student's ID number.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format</h4>
                    <p>Filename: <code>students.csv</code></p>
                    <table>
                        <tr><th>Column</th><th>Description</th><th>Example</th></tr>
                        <tr><td><code>user_id_number</code></td><td>Student ID (unique)</td><td>2024-00001</td></tr>
                        <tr><td><code>name</code></td><td>Full name</td><td>Juan dela Cruz</td></tr>
                        <tr><td><code>email</code></td><td>Email address</td><td>juan@school.edu</td></tr>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.semester-setup.import-students') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-box" onclick="document.getElementById('students_csv').click()">
                        <div class="icon">📄</div>
                        <p>Click to select <strong>students.csv</strong></p>
                        <div class="file-name" id="students_name">No file selected</div>
                        <input type="file" id="students_csv" name="csv_file" accept=".csv,.txt"
                               onchange="document.getElementById('students_name').textContent = this.files[0]?.name || 'No file selected'">
                    </div>
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" class="btn-wizard">Upload &amp; Continue →</button>
                        <a href="{{ route('admin.semester-setup.index', ['step' => 2]) }}" class="btn-skip">Skip this step</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────── STEP 2: Blocks ─────────────────── --}}
        @elseif ($currentStep === 2)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">2</div>
                <h2>🏫 Create Blocks</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV to create blocks for the active semester.
                    Programs must already exist in the system.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format</h4>
                    <p>Filename: <code>blocks.csv</code></p>
                    <table>
                        <tr><th>Column</th><th>Description</th><th>Example</th></tr>
                        <tr><td><code>block_name</code></td><td>Block identifier</td><td>BSIT-2A</td></tr>
                        <tr><td><code>program_code</code></td><td>Program code (must exist)</td><td>BSIT</td></tr>
                        <tr><td><code>year_level</code></td><td>Year level (1–5)</td><td>2</td></tr>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.semester-setup.import-blocks') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-box" onclick="document.getElementById('blocks_csv').click()">
                        <div class="icon">📄</div>
                        <p>Click to select <strong>blocks.csv</strong></p>
                        <div class="file-name" id="blocks_name">No file selected</div>
                        <input type="file" id="blocks_csv" name="csv_file" accept=".csv,.txt"
                               onchange="document.getElementById('blocks_name').textContent = this.files[0]?.name || 'No file selected'">
                    </div>
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" class="btn-wizard">Upload &amp; Continue →</button>
                        <a href="{{ route('admin.semester-setup.index', ['step' => 3]) }}" class="btn-skip">Skip this step</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────── STEP 3: Offerings ─────────────────── --}}
        @elseif ($currentStep === 3)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">3</div>
                <h2>📚 Import Course Offerings</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV of all course offerings for the active semester.
                    Subjects, faculty, and blocks must already exist.
                    <code>block_name</code> and <code>offering_type</code> are optional.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format</h4>
                    <p>Filename: <code>offerings.csv</code></p>
                    <table>
                        <tr><th>Column</th><th>Description</th><th>Example</th></tr>
                        <tr><td><code>subject_code</code></td><td>Course code (must exist)</td><td>CIS2105</td></tr>
                        <tr><td><code>teacher_id_number</code></td><td>Faculty ID number</td><td>2020-00042</td></tr>
                        <tr><td><code>group_number</code></td><td>Section group (optional)</td><td>3</td></tr>
                        <tr><td><code>block_name</code></td><td>Block name (optional)</td><td>BSIT-2A</td></tr>
                        <tr><td><code>offering_type</code></td><td>e.g. Regular (optional)</td><td>Regular</td></tr>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.semester-setup.import-offerings') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-box" onclick="document.getElementById('offerings_csv').click()">
                        <div class="icon">📄</div>
                        <p>Click to select <strong>offerings.csv</strong></p>
                        <div class="file-name" id="offerings_name">No file selected</div>
                        <input type="file" id="offerings_csv" name="csv_file" accept=".csv,.txt"
                               onchange="document.getElementById('offerings_name').textContent = this.files[0]?.name || 'No file selected'">
                    </div>
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" class="btn-wizard">Upload &amp; Continue →</button>
                        <a href="{{ route('admin.semester-setup.index', ['step' => 4]) }}" class="btn-skip">Skip this step</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────── STEP 4: Enrollments ─────────────────── --}}
        @elseif ($currentStep === 4)
        <div class="step-card">
            <div class="step-card-header">
                <div class="step-number">4</div>
                <h2>📋 Import Enrollments</h2>
            </div>
            <div class="step-card-body">
                <p style="font-size:.875rem;color:#6b7280;margin-bottom:1rem;">
                    Upload a CSV of all student enrollments for the active semester.
                    Students and course offerings must already exist.
                    <code>enrollment_type</code> defaults to <em>Block-Enrolled</em> if omitted.
                </p>

                <div class="csv-format">
                    <h4>Required CSV Format</h4>
                    <p>Filename: <code>enrollments.csv</code></p>
                    <table>
                        <tr><th>Column</th><th>Description</th><th>Example</th></tr>
                        <tr><td><code>student_id_number</code></td><td>Student ID (must exist)</td><td>2024-00001</td></tr>
                        <tr><td><code>subject_code</code></td><td>Course code (must exist)</td><td>CIS2105</td></tr>
                        <tr><td><code>group_number</code></td><td>Group (must match offering)</td><td>3</td></tr>
                        <tr><td><code>enrollment_type</code></td><td>Block-Enrolled / Individually-Enrolled (optional)</td><td>Block-Enrolled</td></tr>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.semester-setup.import-enrollments') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-box" onclick="document.getElementById('enrollments_csv').click()">
                        <div class="icon">📄</div>
                        <p>Click to select <strong>enrollments.csv</strong></p>
                        <div class="file-name" id="enrollments_name">No file selected</div>
                        <input type="file" id="enrollments_csv" name="csv_file" accept=".csv,.txt"
                               onchange="document.getElementById('enrollments_name').textContent = this.files[0]?.name || 'No file selected'">
                    </div>
                    <div style="margin-top:1.25rem;display:flex;align-items:center;">
                        <button type="submit" class="btn-wizard">Finish Setup ✓</button>
                    </div>
                </form>

                {{-- Completion summary --}}
                @if ($stepStats[4] > 0)
                <div style="margin-top:1.5rem;padding:1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
                    <p style="font-weight:600;color:#065f46;margin-bottom:.5rem;">✓ Semester Setup Summary</p>
                    <div style="font-size:.875rem;color:#374151;display:grid;grid-template-columns:1fr 1fr;gap:.35rem;">
                        <span>Students registered:</span> <span>{{ number_format($stepStats[1]) }}</span>
                        <span>Blocks created:</span>      <span>{{ number_format($stepStats[2]) }}</span>
                        <span>Course offerings:</span>    <span>{{ number_format($stepStats[3]) }}</span>
                        <span>Enrollments imported:</span><span>{{ number_format($stepStats[4]) }}</span>
                    </div>
                    <a href="{{ route('admin.surveys.global-assign') }}" style="display:inline-block;margin-top:1rem;padding:.55rem 1.25rem;background:#4f46e5;color:#fff;border-radius:7px;font-size:.875rem;font-weight:600;text-decoration:none;">
                        → Proceed to Global Survey Assignment
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

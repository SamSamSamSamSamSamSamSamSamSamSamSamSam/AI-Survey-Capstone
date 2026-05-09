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

                <form action="{{ route('admin.users.import.post') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
<div class="mb-4">
    <label class="form-label fw-bold">Select File</label>
    
    <div class="upload-box @error('csv_file') is-invalid-box @enderror">
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required
               class="upload-input @error('csv_file') is-invalid @enderror">
        
        <label for="csv_file" class="upload-label">
            <div class="upload-icon mb-2">
                <i class="bi bi-file-earmark-spreadsheet text-primary" style="font-size: 2rem;"></i>
            </div>
            <div class="upload-text">
                <span class="fw-bold">Click to upload</span> or drag and drop
                <p class="text-muted small mb-0">CSV files only (Max: 2MB)</p>
            </div>
            <div id="file-name-display" class="mt-2 text-primary fw-semibold small d-none">
                <i class="bi bi-check-circle-fill me-1"></i> <span id="file-name"></span>
            </div>
        </label>
    </div>

    @error('csv_file')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>

                    <div class="d-grid">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                            <span id="btnText"><i class="bi bi-database-add me-2"></i>Start Import & Send Verification Emails</span>
                            <div id="spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
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

@endsection

@push('scripts')
<script>
    document.getElementById('importForm').onsubmit = function() {
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const spinner = document.getElementById('spinner');

        btn.disabled = true;
        text.innerText = "Processing Import...";
        spinner.classList.remove('d-none');
    };
    document.getElementById('csv_file').onchange = function() {
        const fileName = this.files[0]?.name;
        const display = document.getElementById('file-name-display');
        const nameSpan = document.getElementById('file-name');
        const uploadText = document.querySelector('.upload-text');
        const uploadIcon = document.querySelector('.upload-icon');

        if (fileName) {
            nameSpan.innerText = fileName;
            display.classList.remove('d-none');
            uploadText.classList.add('d-none');
            uploadIcon.classList.add('d-none');
        }
    };
</script>
@endpush
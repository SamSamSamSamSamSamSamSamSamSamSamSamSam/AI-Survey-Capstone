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
        <i class="bi bi-exclamation-triangle-fill me-2"> Error Warning:</i>
        <ul class="mb-0 list-unstyled">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h2 class="page-heading">Import Users</h2>
        <p class="page-subheading text-muted">Create multiple accounts using a CSV file and send automated invitations.</p>
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
                        <input type="file" name="csv_file" accept=".csv" required
                            class="form-control @error('csv_file') is-invalid @enderror">
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-2">
                            Only .csv files are supported. Max file size: 2MB.
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                            <span id="btnText"><i class="bi bi-cloud-upload me-2"></i>Start Import & Send Emails</span>
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
                            <tr><td class="font-monospace">user_id_number</td><td class="text-muted italic">2010XXXX</td></tr>
                            <tr><td class="font-monospace">name</td><td class="text-muted italic">John Doe</td></tr>
                            <tr><td class="font-monospace">email</td><td class="text-muted italic">2010XXXX@usc.edu.ph</td></tr>
                            <tr><td class="font-monospace">role</td><td class="text-muted italic">student</td></tr>
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
</script>
@endpush
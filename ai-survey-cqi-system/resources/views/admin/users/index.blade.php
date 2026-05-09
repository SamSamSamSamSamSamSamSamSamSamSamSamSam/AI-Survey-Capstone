@extends('layouts.app')
@section('title', 'User Management')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
</ol>
@endsection

@section('content')

{{-- @if(session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <strong>Success!</strong> {{ session('success') }}
    </div>
@endif --}}

@if($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        <strong>Import Error:</strong>
        <ul style="margin-top: 10px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h2 class="page-heading">Account Directory</h2>
        <p class="page-subheading text-muted">Monitor account status, update profiles, and manage authentication or access permissions.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> New User
        </a>
        
        <a href="{{ route('admin.users.import') }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Users
        </a>
    </div>
</div>

{{-- ===== FILTER CARD ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" id="user-filter-form">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" 
                               id="user-search"
                               value="{{ request('search') }}" 
                               class="form-control auth-input" 
                               placeholder="Name, email, ID…"
                               autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select filter-select">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2"> {{-- Increased to col-md-3 to fit longer text --}}
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select filter-select">
                        <option value="">Active (All)</option>
                        <option value="verified" @selected(request('status') === 'verified')>Verified Only</option>
                        <option value="unverified" @selected(request('status') === 'unverified')>Unverified Only</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Deactivated</option>
                        <option value="all" @selected(request('status') === 'all')>All Records</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-sliders me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE WRAPPER ===== --}}
<div class="card" id="users-table-container">
    @include('admin.users._table')
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('user-filter-form');
    const tableContainer = document.getElementById('users-table-container');
    const searchInput = document.getElementById('user-search');
    const filterSelects = document.querySelectorAll('.filter-select');

    let debounceTimer;

    const performSearch = () => {
        const formData = new FormData(filterForm);
        const queryString = new URLSearchParams(formData).toString();
        const url = `${filterForm.action}?${queryString}`;

        // Visual feedback during load
        tableContainer.style.opacity = '0.6';

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
        })
        .catch(error => {
            console.error('Search failed:', error);
            tableContainer.style.opacity = '1';
        });
    };

    // Dynamic Search (Debounced to 300ms)
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 300);
    });

    // Dynamic Dropdowns
    filterSelects.forEach(select => {
        select.addEventListener('change', performSearch);
    });

    // Prevent full page reload on manual submit
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
});
</script>
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
@extends('layouts.app')
@section('title', 'Surveys')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Surveys</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="page-heading">Survey Management</h2>
        <p class="page-subheading">Design, deploy, and monitor institutional course evaluations.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Survey
        </a>
        <a href="{{ route('admin.surveys.global-assign') }}" class="btn btn-outline-primary">
            <i class="bi bi-send-check-fill me-1"></i> Deploy All
        </a>
    </div>
</div>

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.surveys.index') }}" id="survey-filter-form">
            <div class="row g-3 align-items-end">
                {{-- Semester Filter --}}
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select filter-select">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                                {{ $sem->full_label }} @if ($sem->is_active) · Active @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Input --}}
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" id="survey-search" class="form-control auth-input"
                               placeholder="Title, course code, teacher..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select filter-select">
                        <option value="">All Non-archived</option>
                        <option value="active" @selected(request('status') === 'active')>Active Only</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive Only</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all" @selected(request('status') === 'all')>All Records</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary ">
                        <i class="bi bi-sliders me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary px-3" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" id="surveys-table-wrapper">
    @include('admin.surveys._table')
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('survey-filter-form');
    const tableContainer = document.getElementById('surveys-table-wrapper');
    const searchInput = document.getElementById('survey-search');
    const filterSelects = document.querySelectorAll('.filter-select');

    let debounceTimer;

    const performSearch = () => {
        const formData = new URLSearchParams(new FormData(filterForm)).toString();
        const url = `${filterForm.action}?${formData}`;
        
        tableContainer.style.opacity = '0.6';

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error fetching surveys:', error);
            tableContainer.style.opacity = '1';
        });
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 300);
    });

    filterSelects.forEach(select => select.addEventListener('change', performSearch));
    filterForm.addEventListener('submit', e => { 
        e.preventDefault(); 
        performSearch(); 
    });
});
</script>
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
@extends('layouts.app')
@section('title', 'CQI Reports')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-heading">CQI Reports</h2>
        <p class="page-subheading">AI-generated Continuous Quality Improvement reports.</p>
    </div>
</div>

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
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.cqi-reports.index') }}" class="btn btn-outline-secondary">Reset</a>
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
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('cqi-filter-form');
    const tableContainer = document.getElementById('cqi-table-wrapper');
    const searchInput = document.getElementById('cqi-search');
    const filterSelects = document.querySelectorAll('.filter-select');

    let debounceTimer;

    const performSearch = () => {
        const formData = new URLSearchParams(new FormData(filterForm)).toString();
        tableContainer.style.opacity = '0.6';

        fetch(`${filterForm.action}?${formData}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
        });
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 300);
    });

    filterSelects.forEach(select => select.addEventListener('change', performSearch));
    filterForm.addEventListener('submit', e => { e.preventDefault(); performSearch(); });
});
</script>
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
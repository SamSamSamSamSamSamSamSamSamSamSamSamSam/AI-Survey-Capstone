@extends('layouts.app')
@section('title', 'Subjects')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Subjects</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Subjects</h2>
        <p class="page-subheading">Manage course subjects offered across all programs.</p>
    </div>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Subject
    </a>
</div>

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.subjects.index') }}" id="filter-form">
            <div class="row g-3 align-items-end">

                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search"
                               id="search-input"
                               class="form-control auth-input"
                               placeholder="Search code or name…"
                               value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" id="status-select" class="form-select">
                        <option value="">Active</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all" @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE CONTAINER ===== --}}
<div class="card" id="subjects-table-wrapper">
    @include('admin.subjects.partials.table')
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filter-form');
    const wrapper = document.getElementById('subjects-table-wrapper');
    const searchInput = document.getElementById('search-input');
    const statusSelect = document.getElementById('status-select');

    let debounceTimer;

    const updateTable = () => {
        const queryString = new URLSearchParams(new FormData(form)).toString();
        const url = `${form.action}?${queryString}`;

        window.history.pushState({}, '', url);

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            wrapper.innerHTML = html;
        })
        .catch(error => console.error('Search error:', error));
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateTable, 400);
    });

    statusSelect.addEventListener('change', updateTable);

    wrapper.addEventListener('click', (e) => {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            fetch(link.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                wrapper.innerHTML = html;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });
});
</script>
@endpush
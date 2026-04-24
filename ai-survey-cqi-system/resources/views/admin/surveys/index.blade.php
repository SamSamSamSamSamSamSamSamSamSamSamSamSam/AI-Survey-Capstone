@extends('layouts.app')
@section('title', 'Surveys')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-heading">Surveys</h2>
        <p class="page-subheading">Create and manage course evaluation surveys.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Survey
        </a>
        <a href="{{ route('admin.surveys.global-assign') }}" class="btn btn-outline-primary">
            <i class="bi bi-share me-1"></i> Survey Deployment
        </a>
    </div>

</div>

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.surveys.index') }}" id="survey-filter-form">
            <div class="row g-3 align-items-end">
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

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" id="survey-search" class="form-control auth-input"
                               placeholder="Title, course code, group, or teacher…" value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select filter-select">
                        <option value="">Non-archived</option>
                        <option value="deleted" @selected(request('status') === 'deleted')>Archived</option>
                        <option value="all" @selected(request('status') === 'all')>All</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">Reset</a>
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
        tableContainer.style.opacity = '0.6';

        fetch(`${filterForm.action}?${formData}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            window.history.pushState({}, '', url);
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
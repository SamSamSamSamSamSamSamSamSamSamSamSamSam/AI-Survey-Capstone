@extends('layouts.app')
@section('title', 'Faculty Analytics')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Analytics</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-heading">Faculty Analytics</h2>
        <p class="page-subheading">Survey performance metrics and sentiment analysis across all faculty.</p>
    </div>
    <a href="{{ route('admin.analytics.charts') }}" class="btn btn-primary">
        <i class="bi bi-clipboard-data me-1"></i> View Analytics
    </a>
</div>

{{-- Filter Card --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form id="filter-form" method="GET" action="{{ route('admin.analytics.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Faculty or Course..." value="{{ $search }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                                {{ $sem->full_label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-sliders me-1"></i> Filter</button>
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- This ID is crucial for AJAX --}}
<div id="table-wrapper">
    @include('admin.analytics.partials._table')
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const form = $('#filter-form');
    const wrapper = $('#table-wrapper');

    function updateTable(url) {
        $.ajax({
            url: url,
            success: function(html) {
                wrapper.html(html);
                window.history.pushState({}, '', url);
            }
        });
    }

    form.on('submit', function(e) {
        e.preventDefault();
        updateTable(form.attr('action') + '?' + form.serialize());
    });

    $('input[name="search"], select[name="semester_id"]').on('change keyup', function() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => form.submit(), 300);
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        updateTable($(this).attr('href'));
    });
}); 

</script>
@endpush
@endsection
@extends('layouts.app')
@section('title', 'Faculty Analytics')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Analytics</a></li>
    <li class="breadcrumb-item active">Charts</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Faculty Analytics</h2>
        <p class="page-subheading">
            {{ $totalFaculty }} faculty ·
            {{ $totalAnalytics }} survey(s) analysed
        </p>
    </div>
    <a href="{{ route('admin.analytics.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@if (! $hasData)
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
            <p class="empty-state-text">No analytics data yet.</p>
            <p class="text-muted-sm text-center" style="max-width: 320px;">
                Analytics are computed automatically when surveys are deactivated.
            </p>
        </div>
    </div>
@else

<div class="an-wrap">

{{-- ── Toolbar ── --}}
<div class="an-toolbar d-flex align-items-center gap-3">
    <div class="an-toolbar-group d-flex align-items-center">
        <label class="an-toolbar-label me-2">Faculty</label>
        <select id="sel-faculty" class="an-select">
            <option value="">All Faculty</option>
            @foreach ($faculties as $f)
                <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->user_id_number }})</option>
            @endforeach
        </select>
    </div>

    <div class="an-toolbar-sep"></div>

    <div class="an-toolbar-group d-flex align-items-center">
        <label class="an-toolbar-label me-2">Semester</label>
        <select id="sel-semester" class="an-select">
            <option value="">All Semesters</option>
            @foreach ($semesters as $s)
                <option value="{{ $s->id }}"
                    {{ $activeSemester?->id == $s->id ? 'selected' : '' }}>
                    {{ $s->full_label }}{{ $s->is_active ? ' · Active' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Removed ms-auto and col-md-4 --}}
    <div class="an-toolbar-group d-flex gap-2">
        <button type="button" id="btn-filter" class="btn btn-primary btn-sm ">
            <i class="bi bi-sliders me-1"></i> Filter
        </button>
        <a href="{{ route('admin.analytics.charts') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
        </a>
    </div>
</div>

    {{-- ── Tabs ── --}}
    <div class="an-tabs">
        <button class="an-tab an-tab--active" data-tab="overview">Overview</button>
        <button class="an-tab" data-tab="trends">Trends</button>
        <button class="an-tab" data-tab="categories">Categories</button>
        <button class="an-tab" data-tab="sentiment">Sentiment</button>
        <button class="an-tab" data-tab="benchmark">Benchmarking</button>
        <button class="an-tab" data-tab="pivot">More Charts</button>
    </div>

    @include('admin.analytics.partials._analytics_panels')

</div>

@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
window.ANALYTICS_CONFIG = {
    baseUrl:           '/api/analytics',
    passingThreshold:  {{ setting('survey.passing_threshold', 3.0) }},
    hasFacultyFilter:  true,
    activeSemesterId:  '{{ $activeSemester?->id ?? '' }}',
};
</script>
{{-- analytics-charts.js wires up #btn-filter itself via addEventListener inside init() --}}
@vite(['resources/js/modules/analytics-charts.js'])
@endpush
@extends('layouts.app')
@section('title', 'My Analytics')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Analytics</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">My Analytics</h2>
        <p class="page-subheading">Based on completed student evaluations for your courses.</p>
    </div>
    @if ($activeSemester)
        <span class="status-pill status-pill--active" style="font-size:.72rem;">
            <i class="bi bi-calendar-check me-1"></i>{{ $activeSemester->full_label }}
        </span>
    @endif
</div>

@if (! $hasData)
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
            <p class="empty-state-text">No analytics data yet.</p>
            <p class="text-muted-sm text-center" style="max-width: 320px;">
                Analytics are computed automatically after your surveys are deactivated.
            </p>
        </div>
    </div>
@else

<div class="an-wrap">

    {{-- ── Semester filter only (no faculty selector for faculty role) ── --}}
    <div class="an-toolbar">
        <div class="an-toolbar-group">
            <label class="an-toolbar-label">Semester</label>
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
    </div>

    {{-- ── Tabs ── --}}
    <div class="an-tabs">
        <button class="an-tab an-tab--active" data-tab="overview">Overview</button>
        <button class="an-tab" data-tab="trends">Trends</button>
        <button class="an-tab" data-tab="categories">Categories</button>
        <button class="an-tab" data-tab="sentiment">Sentiment</button>
        <button class="an-tab" data-tab="benchmark">Benchmarking</button>
        <button class="an-tab" data-tab="pivot">Pivot Explorer</button>
    </div>

    @include('faculty.analytics._panels')

</div>

@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
window.ANALYTICS_CONFIG = {
    baseUrl:           '/api/analytics',
    passingThreshold:  {{ setting('survey.passing_threshold', 3.0) }},
    hasFacultyFilter:  false,
    activeSemesterId:  '{{ $activeSemester?->id ?? '' }}',
};
</script>
@vite(['resources/js/modules/analytics-charts.js'])
{{-- <script src="{{ asset('js/modules/analytics-charts.js') }}" defer></script> --}}
@endpush
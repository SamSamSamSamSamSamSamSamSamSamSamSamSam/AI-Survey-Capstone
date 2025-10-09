@extends('layouts.default')

@section('header')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-semibold">Admin Dashboard</h1>
        <div>
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle me-1"></i> Create Survey
            </a>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> View Surveys
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <x-admin.metric icon="fa-list" color="primary" value="{{ $totalSurveys }}" label="Total Surveys" />
        <x-admin.metric icon="fa-user-tie" color="success" value="{{ $facultyCount }}" label="Faculty Evaluated" />
        <x-admin.metric icon="fa-users" color="info" value="{{ $studentCount }}" label="Student Respondents" />
        <x-admin.metric icon="fa-star" color="warning" value="{{ $avgRating }}" label="Average Rating" />
        <x-admin.metric icon="fa-file-lines" color="danger" value="{{ $cqiReports }}" label="CQI Reports" />
    </div>

    {{-- Charts --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card p-3">
                <h6 class="fw-semibold mb-2">Department Performance</h6>
                <canvas id="deptPerformanceChart"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h6 class="fw-semibold mb-2">Survey Participation</h6>
                <canvas id="participationChart"></canvas>
            </div>
            <div class="card p-3">
                <h6 class="fw-semibold mb-2">Feedback Sentiment</h6>
                <canvas id="sentimentChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card p-3">
                <h6 class="fw-semibold mb-2">Faculty Performance</h6>
                <canvas id="facultyPerformanceChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-3">
                <h6 class="fw-semibold mb-2">Top Performing Faculty</h6>
                <canvas id="topFacultyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <h6 class="fw-semibold mb-2">Sentiment Trend</h6>
        <canvas id="sentimentTrendChart"></canvas>
    </div>

</div>
@endsection

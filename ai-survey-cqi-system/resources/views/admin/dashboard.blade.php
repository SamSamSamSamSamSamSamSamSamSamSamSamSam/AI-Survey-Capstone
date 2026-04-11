@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
@endsection

@section('content')

<div class="dashboard">

    {{-- ===== DASHBOARD HEADER ===== --}}
    <div class="dashboard-header d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="dashboard-title">
                Welcome back, {{ Str::words(auth()->user()->name) }}
                <span class="role-badge">Admin</span>
            </h2>
            <p class="dashboard-subtitle">
                Here's what's happening in your CQI system today.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Survey
            </a>
            <a href="{{ route('admin.cqi-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clipboard-data me-1"></i> CQI Reports
            </a>
        </div>
    </div>

    {{-- ===== KPI CARDS ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--blue">
                        <i class="bi bi-ui-checks-grid"></i>
                    </div>
                    <div>
                        <p class="kpi-label">Total Surveys</p>
                        <h3 class="kpi-value">{{ $totalSurveys ?? '--' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--success">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div>
                        <p class="kpi-label">Responses Collected</p>
                        <h3 class="kpi-value">{{ $totalResponses ?? '--' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--accent">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div>
                        <p class="kpi-label">CQI Reports Generated</p>
                        <h3 class="kpi-value">{{ $totalReports ?? '--' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon kpi-icon--navy">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <p class="kpi-label">Total Users</p>
                        <h3 class="kpi-value">{{ $totalUsers ?? '--' }}</h3>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== MAIN GRID ===== --}}
    <div class="row g-3">

        {{-- Analytics Chart --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Analytics Overview</h5>
                        <span class="badge text-bg-secondary" style="font-size:.7rem; font-weight:500;">
                            Live Data
                        </span>
                    </div>

                    {{-- Replace with actual chart component when data is wired up --}}
                    <div class="ai-skeleton" style="height: 200px;"></div>

                    <div class="mt-3 d-flex gap-4">
                        <div class="skeleton-line" style="width: 30%; height:10px;"></div>
                        <div class="skeleton-line" style="width: 25%; height:10px;"></div>
                        <div class="skeleton-line" style="width: 20%; height:10px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Status --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">System Status</h5>

                    <ul class="status-list">
                        <li>
                            <span class="status-dot success"></span>
                            Survey Module Active
                        </li>
                        <li>
                            <span class="status-dot pending"></span>
                            AI Processing Idle
                        </li>
                        <li>
                            <span class="status-dot success"></span>
                            Database Connected
                        </li>
                    </ul>

                    <hr class="my-3" style="opacity:.08">

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.analytics.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-bar-chart me-1"></i> View Full Analytics
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-people me-1"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Surveys (placeholder) --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Recent Surveys</h5>
                        <a href="{{ route('admin.surveys.index') }}"
                           class="btn btn-sm btn-outline-secondary">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    {{-- Skeleton rows while data loads --}}
                    @forelse($recentSurveys ?? [] as $survey)
                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ $survey->title }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $survey->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="badge text-bg-primary" style="font-size:.7rem">Active</span>
                        </div>
                    @empty
                        {{-- Loading skeleton placeholder --}}
                        @for($i = 0; $i < 4; $i++)
                            <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <div class="skeleton-line" style="width: 40%; height:12px; margin-bottom:6px"></div>
                                    <div class="skeleton-line" style="width: 20%; height:10px"></div>
                                </div>
                                <div class="skeleton-line" style="width: 54px; height:22px; border-radius:99px; margin:0"></div>
                            </div>
                        @endfor
                    @endforelse

                </div>
            </div>
        </div>

    </div>{{-- /.row --}}

</div>{{-- /.dashboard --}}

@endsection
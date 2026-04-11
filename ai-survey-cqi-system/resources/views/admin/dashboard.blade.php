@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard">


{{-- HEADER --}}
<div class="dashboard-header">

    <div>
        <h1 class="dashboard-title">
            Dashboard
            <span class="role-badge">Admin</span>
        </h1>

        <p class="dashboard-subtitle">
            {{ auth()->user()->name }} · {{ auth()->user()->user_id_number }}
        </p>
    </div>

</div>

{{-- KPI CARDS --}}
<div class="row g-4">

    <div class="col-md-4">
        <div class="card kpi-card">
            <div class="card-body">
                <p class="kpi-label">Total Surveys</p>
                <h3 class="kpi-value">--</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card kpi-card">
            <div class="card-body">
                <p class="kpi-label">Responses Collected</p>
                <h3 class="kpi-value">--</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card kpi-card">
            <div class="card-body">
                <p class="kpi-label">CQI Reports Generated</p>
                <h3 class="kpi-value">--</h3>
            </div>
        </div>
    </div>

</div>

{{-- MAIN CONTENT --}}
<div class="row g-4 mt-1">

    {{-- Analytics Placeholder --}}
    <div class="col-lg-8">

        <div class="card">

            <div class="card-body">

                <h5 class="card-title">Analytics Overview</h5>

                <div class="ai-skeleton" style="height: 180px;"></div>

            </div>

        </div>

    </div>

    {{-- Activity / Status --}}
    <div class="col-lg-4">

        <div class="card">

            <div class="card-body">

                <h5 class="card-title">System Status</h5>

                <ul class="status-list">
                    <li><span class="status-dot success"></span> Survey Module Active</li>
                    <li><span class="status-dot pending"></span> AI Processing Idle</li>
                    <li><span class="status-dot success"></span> Database Connected</li>
                </ul>

            </div>

        </div>

    </div>

</div>

</div>

@endsection


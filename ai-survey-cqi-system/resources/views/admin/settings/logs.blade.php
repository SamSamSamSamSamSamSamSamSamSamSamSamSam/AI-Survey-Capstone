@extends('layouts.app')
@section('title', 'Settings Audit Log')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Audit Log</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Settings Audit Log</h2>
        <p class="page-subheading">Track all changes made to system settings.</p>
    </div>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Settings
    </a>
</div>

{{-- ===== FILTERS ===== --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.settings.logs') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Group</label>
                    <select name="group" class="form-select">
                        <option value="">All Groups</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g }}" @selected(request('group') === $g)>
                                {{ ucfirst($g) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search Key</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="key"
                               class="form-control auth-input"
                               placeholder="e.g. ai.gemini_api_key"
                               value="{{ request('key') }}">
                    </div>
                </div>

                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-sliders me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.settings.logs') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== LOG TABLE ===== --}}
<div class="card">
    @if ($logs->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-clock-history"></i></div>
            <p class="empty-state-text">No audit log entries found.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Setting Key</th>
                        <th>Group</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Changed By</th>
                        <th>Changed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                    <tr>
                        <td>
                            {{-- <span class="program-code-badge" style="font-size:.72rem;">
                                {{ $log->key }}
                            </span> --}}
                            {{ $log->setting?->label ?? $log->key }}
                        </td>
                        <td>
                            <span class="scope-badge scope-badge--survey"
                                  style="text-transform:uppercase;font-size:.65rem;">
                                {{ $log->group }}
                            </span>
                        </td>
                        <td>
                            <span class="settings-log-val settings-log-val--old">
                                {{ Str::limit($log->old_value ?? '—', 40) }}
                            </span>
                        </td>
                        <td>
                            <span class="settings-log-val">
                                {{ Str::limit($log->new_value ?? '—', 40) }}
                            </span>
                        </td>
                        <td class="fw-500" style="font-size:.845rem;">
                            {{ $log->changed_by_name }}
                        </td>
                        <td class="text-muted-sm">
                            {{ $log->changed_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="table-pagination">{{ $logs->links() }}</div>
        @endif
    @endif
</div>

@endsection
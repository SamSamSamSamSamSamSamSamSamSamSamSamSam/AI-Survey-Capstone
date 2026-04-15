@extends('layouts.app')
@section('title', 'Reports')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Reports</li>
</ol>
@endsection

@push('styles')
    <style>
        /* *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; display: flex; min-height: 100vh; }
        .sidebar { width: 200px; background: #1e3a5f; color: #bfdbfe; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 1.25rem 1rem; font-size: 1rem; font-weight: 700; color: #fff; border-bottom: 1px solid #1e40af; }
        .sidebar-brand span { font-size: .7rem; display: block; color: #93c5fd; font-weight: 400; }
        .sidebar-nav { flex: 1; padding: .75rem 0; }
        .nav-link { display: block; padding: .5rem 1rem; font-size: .85rem; color: #bfdbfe; text-decoration: none; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: #1e40af; color: #fff; }
        .sidebar-footer { padding: .75rem 1rem; border-top: 1px solid #1e40af; font-size: .8rem; color: #93c5fd; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-title { font-size: 1rem; font-weight: 600; }
        .topbar-user { font-size: .825rem; color: #6b7280; display: flex; align-items: center; gap: .75rem; }
        .btn-logout { background: none; border: 1px solid #e5e7eb; padding: .35rem .85rem; border-radius: 6px; font-size: .8rem; cursor: pointer; color: #374151; }
        .content { flex: 1; padding: 1.75rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.25rem; } */
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; margin-bottom: 1rem; }
        .card-body { padding: 1.25rem; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead { background: #f9fafb; }
        th { padding: .65rem 1rem; text-align: left; font-weight: 600; color: #6b7280; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb; }
        td { padding: .7rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .btn { display: inline-block; padding: .4rem .9rem; border-radius: 6px; font-size: .82rem; cursor: pointer; text-decoration: none; border: none; font-weight: 500; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .empty-state { text-align: center; padding: 3rem; color: #9ca3af; }
        .badge { display: inline-block; padding: .2rem .5rem; border-radius: 999px; font-size: .7rem; font-weight: 600; }
        .pagination { display: flex; gap: .3rem; justify-content: flex-end; padding: .9rem 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .3rem .65rem; border-radius: 5px; font-size: .78rem; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 7px; padding: .7rem 1rem; margin-bottom: 1.25rem; font-size: .875rem; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 7px; padding: .7rem 1rem; margin-bottom: 1.25rem; font-size: .875rem; }
    </style>
@endpush

@section('content')
    <div class="content">

        @if (session('success')) <div class="alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert-error">{{ session('error') }}</div>     @endif

        <div class="page-header"><h1>My CQI Reports</h1></div>

        <div class="card">
            @if ($reports->isEmpty())
                <p class="empty-state">No CQI reports have been generated for your courses yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Scope</th>
                            <th>Generated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                        <tr>
                            <td>
                                <div style="font-weight:500;">{{ $report->survey?->offering?->subject?->course_code }}</div>
                                <div style="font-size:.78rem;color:#6b7280;">{{ $report->survey?->offering?->subject?->name }}</div>
                            </td>
                            <td style="font-size:.82rem;">{{ $report->survey?->offering?->semester?->full_label }}</td>
                            <td><span class="badge" style="background:#e0e7ff;color:#3730a3;text-transform:uppercase;">{{ $report->scope_type }}</span></td>
                            <td style="font-size:.78rem;color:#6b7280;">{{ $report->created_at->format('M d, Y') }}</td>
                            <td>
                                <div style="display:flex;gap:.4rem;">
                                    <a href="{{ route('faculty.reports.show', $report->id) }}" class="btn btn-secondary" style="font-size:.78rem;padding:.3rem .7rem;">View</a>
                                    <a href="{{ route('faculty.reports.download', $report->id) }}" class="btn btn-primary" style="font-size:.78rem;padding:.3rem .7rem;">↓ PDF</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination">{{ $reports->links('pagination::simple-tailwind') }}</div>
            @endif
        </div>
    </div>
@endsection

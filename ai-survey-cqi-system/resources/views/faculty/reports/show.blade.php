@extends('layouts.app')
@section('title', 'My Reports')

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
        .nav-link { display: block; padding: .5rem 1rem; font-size: .85rem; color: #bfdbfe; text-decoration: none; }
        .nav-link:hover, .nav-link.active { background: #1e40af; color: #fff; }
        .sidebar-footer { padding: .75rem 1rem; border-top: 1px solid #1e40af; font-size: .8rem; color: #93c5fd; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-title { font-size: 1rem; font-weight: 600; }
        .topbar-right { display: flex; align-items: center; gap: .75rem; font-size: .825rem; color: #6b7280; }
        .btn-logout { background: none; border: 1px solid #e5e7eb; padding: .35rem .85rem; border-radius: 6px; font-size: .8rem; cursor: pointer; color: #374151; }
        .content { flex: 1; padding: 1.75rem; max-width: 900px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.25rem; } */

        /* Report sections */
        .report-meta { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1rem; border-left: 4px solid #1e3a5f; }
        .report-meta table { font-size: .875rem; width: 100%; }
        .report-meta td:first-child { color: #6b7280; width: 160px; padding: .3rem 0; }

        .section { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1rem; overflow: hidden; }
        .section-head { padding: .65rem 1rem; font-weight: 600; font-size: .875rem; color: #fff; }
        .section-head.green  { background: #065f46; }
        .section-head.blue   { background: #1e3a5f; }
        .section-head.orange { background: #92400e; }
        .section-head.indigo { background: #3730a3; }
        .section-head.slate  { background: #374151; }
        .section-body { padding: 1rem 1.25rem; }

        .bullet-list p { font-size: .875rem; color: #374151; padding: .3rem 0; line-height: 1.5; border-bottom: 1px solid #f9fafb; }
        .bullet-list p:last-child { border-bottom: none; }

        .action-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        .action-table th { padding: .55rem .75rem; background: #f9fafb; color: #6b7280; font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .action-table td { padding: .55rem .75rem; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .action-table tr:last-child td { border-bottom: none; }
        .action-table tr:nth-child(even) td { background: #fafafa; }

        .interp-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 7px; padding: .9rem 1.1rem; font-size: .9rem; color: #1e3a5f; line-height: 1.6; margin-bottom: 1rem; }

        .btn { display: inline-block; padding: .45rem 1rem; border-radius: 6px; font-size: .85rem; cursor: pointer; text-decoration: none; border: none; font-weight: 500; }
        .btn-primary   { background: #4f46e5; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
    </style>
@endpush

@section('content')
    <div class="content">

        <div class="page-header">
            <h1>CQI Report</h1>
            <div style="display:flex;gap:.65rem;">
                <a href="{{ route('faculty.reports.download', $cqiReport->id) }}" class="btn btn-primary">↓ Download PDF</a>
                <a href="{{ route('faculty.reports.index') }}" class="btn btn-secondary">← Back</a>
            </div>
        </div>

        {{-- Meta --}}
        <div class="report-meta">
            <table>
                <tr><td>Course</td><td><strong>{{ $cqiReport->survey?->offering?->subject?->course_code }} — {{ $cqiReport->survey?->offering?->subject?->name }}</strong></td></tr>
                <tr><td>Semester</td><td>{{ $cqiReport->survey?->offering?->semester?->full_label }}</td></tr>
                <tr><td>Report Scope</td><td style="text-transform:uppercase;font-size:.8rem;font-weight:600;">{{ $cqiReport->scope_type }}</td></tr>
                <tr><td>Generated On</td><td>{{ $cqiReport->created_at->format('F d, Y') }}</td></tr>
            </table>
        </div>

        @php $ai = $cqiReport->report_text; @endphp

        {{-- Overall interpretation --}}
        @if (!empty($ai['overall_interpretation']))
            <div class="interp-banner">{{ $ai['overall_interpretation'] }}</div>
        @endif

        {{-- Strengths --}}
        @if (!empty($ai['strengths']))
        <div class="section">
            <div class="section-head green">✓ Strengths Identified</div>
            <div class="section-body bullet-list">
                @foreach ($ai['strengths'] as $s)
                    <p>• {{ $s }}</p>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Areas for improvement --}}
        @if (!empty($ai['areas_for_improvement']))
        <div class="section">
            <div class="section-head orange">⚠ Areas for Improvement</div>
            <div class="section-body bullet-list">
                @foreach ($ai['areas_for_improvement'] as $a)
                    <p>• {{ $a }}</p>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Action plan --}}
        @if (!empty($ai['action_plan']))
        <div class="section">
            <div class="section-head indigo">Action Plan</div>
            <div class="section-body" style="padding:0;">
                <table class="action-table">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Action</th>
                            <th>Responsible</th>
                            <th>Timeline</th>
                            <th>Expected Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ai['action_plan'] as $ap)
                        <tr>
                            <td>{{ $ap['area'] ?? '' }}</td>
                            <td>{{ $ap['action'] ?? '' }}</td>
                            <td>{{ $ap['responsible_person'] ?? '' }}</td>
                            <td>{{ $ap['timeline'] ?? '' }}</td>
                            <td>{{ $ap['expected_outcome'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Monitoring --}}
        @if (!empty($ai['monitoring']))
        <div class="section">
            <div class="section-head blue">Monitoring and Evaluation</div>
            <div class="section-body bullet-list">
                @foreach ($ai['monitoring'] as $m)
                    <p>• {{ $m }}</p>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Conclusion --}}
        @if (!empty($ai['conclusion']))
        <div class="section">
            <div class="section-head slate">Conclusion</div>
            <div class="section-body">
                <p style="font-size:.9rem;line-height:1.7;color:#374151;">{{ $ai['conclusion'] }}</p>
            </div>
        </div>
        @endif

    </div>
@endsection
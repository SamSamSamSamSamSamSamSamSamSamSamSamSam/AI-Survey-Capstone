@extends('layouts.app')

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">My Enrollments</li>
    </ol>
@endsection

@push('styles')
    <style>
        /* *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; display: flex; min-height: 100vh; } */

        /* Sidebar */
        /* .sidebar { width: 200px; background: #064e3b; color: #a7f3d0; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 1.25rem 1rem; font-size: 1rem; font-weight: 700; color: #fff; border-bottom: 1px solid #065f46; }
        .sidebar-brand span { font-size: .7rem; display: block; color: #6ee7b7; font-weight: 400; }
        .sidebar-nav { flex: 1; padding: .75rem 0; }
        .nav-link { display: block; padding: .5rem 1rem; font-size: .85rem; color: #a7f3d0; text-decoration: none; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: #065f46; color: #fff; }
        .sidebar-footer { padding: .75rem 1rem; border-top: 1px solid #065f46; font-size: .8rem; color: #6ee7b7; } */

        /* Main */
        /* .main { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-title { font-size: 1rem; font-weight: 600; }
        .topbar-user { font-size: .825rem; color: #6b7280; display: flex; align-items: center; gap: .75rem; }
        .btn-logout { background: none; border: 1px solid #e5e7eb; padding: .35rem .85rem; border-radius: 6px; font-size: .8rem; cursor: pointer; color: #374151; }
        .content { flex: 1; padding: 1.75rem; } */

        /* Alerts */
        .alert { padding: .7rem 1rem; border-radius: 7px; font-size: .875rem; margin-bottom: 1.25rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .alert-warn    { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; }

        /* .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.25rem; } */

        /* Section label */
        /* .section-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: .75rem; } */

        /* Cards */
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; margin-bottom: 1.5rem; }
        .card-header { padding: .75rem 1rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: .875rem; }

        /* Offering grid */
        .offering-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
        .offering-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; }
        .offering-card:hover { border-color: #a5b4fc; box-shadow: 0 2px 8px rgba(99,102,241,.1); }
        .offering-card .code { font-size: .75rem; color: #6b7280; margin-bottom: .2rem; }
        .offering-card .name { font-weight: 600; font-size: .95rem; margin-bottom: .5rem; }
        .offering-card .meta { font-size: .8rem; color: #6b7280; margin-bottom: .75rem; line-height: 1.5; }
        .offering-card .badge { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .7rem; font-weight: 600; background: #dbeafe; color: #1d4ed8; margin-bottom: .5rem; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead { background: #f9fafb; }
        th { padding: .65rem 1rem; text-align: left; font-weight: 600; color: #6b7280; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb; }
        td { padding: .7rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .btn { display: inline-block; padding: .4rem .9rem; border-radius: 6px; font-size: .82rem; cursor: pointer; text-decoration: none; border: none; font-weight: 500; }
        .btn-primary { background: #059669; color: #fff; }
        .btn-primary:hover { background: #047857; }
        .btn-danger  { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #fecaca; }

        .badge-status { display: inline-block; padding: .2rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 600; background: #d1fae5; color: #065f46; }
        .empty-state { text-align: center; padding: 2.5rem; color: #9ca3af; font-size: .9rem; }
    </style>
@endpush

@section('content')
<div class="main">
    {{-- <div class="topbar">
        <span class="topbar-title">My Enrollments</span>
        <div class="topbar-user">
            {{ auth()->user()->user_id_number }}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </div> --}}

    <div class="content">

        {{-- @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif --}}

        {{-- ------------------------------------------------------------------ --}}
        {{-- Available offerings to enroll in (active semester only)            --}}
        {{-- ------------------------------------------------------------------ --}}
        <div class="page-header">
            <h1>Enroll in a Course</h1>
        </div>

        @if (! $activeSemester)
            <div class="alert alert-warn">
                Enrollment is currently unavailable. No active semester has been set by the administrator.
            </div>
        @elseif ($availableOfferings->isEmpty())
            <div class="card">
                <p class="empty-state">You are already enrolled in all available courses for <strong>{{ $activeSemester->full_label }}</strong>.</p>
            </div>
        @else
            <div class="alert alert-info">
                Enrolling for: <strong>{{ $activeSemester->full_label }}</strong>
            </div>

            <div class="offering-grid" style="margin-bottom:2rem;">
                @foreach ($availableOfferings as $offering)
                <div class="offering-card">
                    <div class="code">{{ $offering->subject->course_code }}</div>
                    <div class="name">{{ $offering->subject->name }}</div>
                    @if ($offering->offeringType)
                        <span class="badge">{{ $offering->offeringType->name }}</span>
                    @endif
                    <div class="meta">
                        <div>👤 {{ $offering->teacher->name }}</div>
                        <div>📚 {{ $offering->subject->units }} unit(s)</div>
                        @if ($offering->group_number)
                            <div>Group {{ $offering->group_number }}</div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('student.enrollments.store') }}">
                        @csrf
                        <input type="hidden" name="offering_id" value="{{ $offering->id }}">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Enroll</button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif

        {{-- ------------------------------------------------------------------ --}}
        {{-- My enrollment history — all semesters                              --}}
        {{-- ------------------------------------------------------------------ --}}
        <p class="section-label">My Enrollment History</p>

        <div class="card">
            @if ($myEnrollments->isEmpty())
                <p class="empty-state">You have no enrollment records yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Faculty</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Enrolled On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($myEnrollments as $enrollment)
                        <tr>
                            <td>
                                <div style="font-weight:500;">{{ $enrollment->offering->subject->name }}</div>
                                <div style="font-size:.78rem;color:#6b7280;">{{ $enrollment->offering->subject->course_code }}</div>
                            </td>
                            <td style="font-size:.85rem;">{{ $enrollment->offering->teacher->name }}</td>
                            <td style="font-size:.8rem;">{{ $enrollment->offering->semester->full_label }}</td>
                            <td><span class="badge-status">{{ ucfirst($enrollment->enrollmentType->name) }}</span></td>
                            <td style="font-size:.8rem;">{{ $enrollment->created_at->format('M d, Y') }}</td>
                            <td>
                                {{-- Only allow dropping from the active semester --}}
                                @if ($activeSemester && $enrollment->offering->semester_id === $activeSemester->id)
                                    <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment->id) }}" onsubmit="return confirm('Drop this course?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger">Drop</button>
                                    </form>
                                @else
                                    <span style="font-size:.78rem;color:#9ca3af;">Completed</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</div>
@endsection

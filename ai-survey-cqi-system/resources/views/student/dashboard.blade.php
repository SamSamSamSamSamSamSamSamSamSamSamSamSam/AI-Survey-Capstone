<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard — CQI System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; display: flex; min-height: 100vh; font-size: 14px; }

        /* ── Sidebar ── */
        .sidebar { width: 220px; background: #064e3b; color: #a7f3d0; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 1.25rem 1rem; border-bottom: 1px solid #065f46; }
        .sidebar-brand h2 { font-size: 1rem; font-weight: 700; color: #fff; }
        .sidebar-brand span { font-size: .72rem; color: #6ee7b7; }
        .sidebar-nav { flex: 1; padding: .5rem 0; }
        .nav-section { font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; color: #34d399; padding: .75rem 1rem .25rem; }
        .nav-link { display: flex; align-items: center; gap: .6rem; padding: .5rem 1rem; font-size: .83rem; color: #a7f3d0; text-decoration: none; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: #065f46; color: #fff; }
        .nav-link .icon { width: 18px; text-align: center; font-size: .95rem; }
        .sidebar-user { padding: .85rem 1rem; border-top: 1px solid #065f46; }
        .sidebar-user .name { font-size: .82rem; font-weight: 600; color: #fff; }
        .sidebar-user .role { font-size: .72rem; color: #6ee7b7; }
        .btn-logout { margin-top: .5rem; background: none; border: 1px solid #059669; color: #6ee7b7; padding: .3rem .75rem; border-radius: 5px; font-size: .75rem; cursor: pointer; width: 100%; }
        .btn-logout:hover { background: #065f46; color: #fff; }

        /* ── Main ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-left h1 { font-size: 1.1rem; font-weight: 700; color: #064e3b; }
        .topbar-left p  { font-size: .8rem; color: #6b7280; margin-top: .1rem; }
        .semester-badge { background: #d1fae5; color: #065f46; padding: .2rem .65rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }

        .content { flex: 1; padding: 1.5rem; overflow-y: auto; }

        /* ── Stat cards ── */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: #fff; border-radius: 10px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-icon.green  { background: #d1fae5; }
        .stat-icon.blue   { background: #dbeafe; }
        .stat-icon.yellow { background: #fef3c7; }
        .stat-icon.red    { background: #fee2e2; }
        .stat-val   { font-size: 1.6rem; font-weight: 700; line-height: 1; color: #111; }
        .stat-label { font-size: .75rem; color: #6b7280; margin-top: .2rem; }

        /* ── Section headers ── */
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: .75rem; }
        .section-header h2 { font-size: .95rem; font-weight: 700; color: #064e3b; }
        .section-header a, .section-header button  { font-size: .8rem; color: #059669; text-decoration: none; background: none; border: none; cursor: pointer; }

        /* ── Cards ── */
        .card { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; margin-bottom: 1.25rem; }
        .card-head { padding: .65rem 1rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: .82rem; color: #374151; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 1rem; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }
        th { padding: .5rem .85rem; text-align: left; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        td { padding: .6rem .85rem; font-size: .83rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafa; }

        /* ── Badges ── */
        .badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; font-size: .7rem; font-weight: 600; }
        .badge-active   { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #f3f4f6; color: #6b7280; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-done     { background: #dbeafe; color: #1d4ed8; }
        .badge-block    { background: #ede9fe; color: #5b21b6; }
        .badge-indiv    { background: #fce7f3; color: #9d174d; }

        /* ── Pending survey card ── */
        .survey-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.1rem; margin-bottom: .75rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; transition: border-color .15s, box-shadow .15s; }
        .survey-card:hover { border-color: #a7f3d0; box-shadow: 0 2px 8px rgba(6,95,70,.08); }
        .survey-card.urgent { border-left: 4px solid #dc2626; }
        .survey-card-info { flex: 1; }
        .survey-card-title { font-weight: 600; font-size: .9rem; margin-bottom: .3rem; }
        .survey-card-meta  { font-size: .75rem; color: #6b7280; display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
        .meta-chip { background: #f3f4f6; padding: .15rem .45rem; border-radius: 4px; }
        .deadline-chip { background: #fef2f2; color: #b91c1c; padding: .15rem .45rem; border-radius: 4px; font-weight: 500; }
        .deadline-chip.safe { background: #f0fdf4; color: #065f46; }
        .btn-take { display: inline-block; padding: .45rem 1.1rem; background: #059669; color: #fff; border-radius: 7px; font-size: .82rem; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .btn-take:hover { background: #047857; }

        /* ── Empty state ── */
        .empty { text-align: center; padding: 2rem; color: #9ca3af; font-size: .85rem; }
        .empty .big { font-size: 2rem; margin-bottom: .5rem; }

        /* ── Alerts ── */
        .alert { padding: .65rem 1rem; border-radius: 7px; font-size: .82rem; margin-bottom: 1rem; }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-warn    { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }

        /* ── Toggle ── */
        .toggle-btn { font-size: .8rem; color: #059669; background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 5px; padding: .25rem .7rem; text-decoration: none; }
        .toggle-btn:hover { background: #d1fae5; }

        /* ── Two column layout ── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }

        /* ── Progress bar (survey completion) ── */
        .progress-wrap { margin-top: .5rem; }
        .progress-track { height: 6px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
        .progress-fill { height: 100%; background: #059669; border-radius: 999px; }
    </style>
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>CQI System</h2>
        <span>Student Portal</span>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Main</p>
        <a href="{{ route('student.dashboard') }}" class="nav-link active">
            <span class="icon">🏠</span> Dashboard
        </a>
        <a href="{{ route('student.enrollments.index') }}" class="nav-link">
            <span class="icon">📚</span> My Enrollments
        </a>
        <a href="{{ route('survey.index') }}" class="nav-link">
            <span class="icon">📋</span> My Surveys
        </a>
    </nav>
    <div class="sidebar-user">
        <div class="name">{{ $user->name }}</div>
        <div class="role">{{ $user->user_id_number }} · Student</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Sign Out</button>
        </form>
    </div>
</aside>

{{-- ── Main ── --}}
<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h1>Hello, {{ explode(' ', $user->name)[0] }} 👋</h1>
            <p>Here's what's happening with your courses and surveys.</p>
        </div>
        <div>
            @if ($activeSemester)
                <span class="semester-badge">{{ $activeSemester->full_label }}</span>
            @else
                <span style="color:#9ca3af;font-size:.82rem;">No active semester</span>
            @endif
        </div>
    </div>

    <div class="content">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;">{{ session('error') }}</div>
        @endif

        @if (! $activeSemester)
            <div class="alert alert-info">No active semester is currently set. Enrollment and surveys are unavailable.</div>
        @endif

        {{-- ── Stat Cards ── --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon green">📚</div>
                <div>
                    <div class="stat-val">{{ $activeSemEnrolled }}</div>
                    <div class="stat-label">Enrolled This Semester</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">⏳</div>
                <div>
                    <div class="stat-val">{{ $pendingCount }}</div>
                    <div class="stat-label">Pending Surveys</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">✅</div>
                <div>
                    <div class="stat-val">{{ $totalCompleted }}</div>
                    <div class="stat-label">Surveys Completed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon {{ $pendingCount > 0 ? 'red' : 'green' }}">
                    {{ $pendingCount > 0 ? '🔔' : '🎉' }}
                </div>
                <div>
                    <div class="stat-val">{{ $pendingCount > 0 ? $pendingCount : '0' }}</div>
                    <div class="stat-label">{{ $pendingCount > 0 ? 'Action Required' : 'All Done!' }}</div>
                </div>
            </div>
        </div>

        {{-- ── Pending Surveys (prominent) ── --}}
        <div class="section-header">
            <h2>⏳ Pending Surveys
                @if ($pendingCount > 0)
                    <span style="background:#fef3c7;color:#92400e;font-size:.72rem;padding:.1rem .45rem;border-radius:4px;margin-left:.4rem;">{{ $pendingCount }} to complete</span>
                @endif
            </h2>
            <a href="{{ route('survey.index') }}">View All Surveys →</a>
        </div>

        @if ($pendingSurveys->isEmpty())
            <div class="card">
                <div class="empty">
                    <div class="big">🎉</div>
                    <p>You have no pending surveys. You're all caught up!</p>
                </div>
            </div>
        @else
            @foreach ($pendingSurveys as $survey)
            @php
                $daysLeft   = $survey->end_date ? now()->diffInDays($survey->end_date, false) : null;
                $isUrgent   = $daysLeft !== null && $daysLeft <= 2;
                $deadlineCls = $daysLeft !== null && $daysLeft > 3 ? 'safe' : '';
            @endphp
            <div class="survey-card {{ $isUrgent ? 'urgent' : '' }}">
                <div class="survey-card-info">
                    <div class="survey-card-title">{{ $survey->title }}</div>
                    <div class="survey-card-meta">
                        <span class="meta-chip">📚 {{ $survey->offering->subject->course_code }}</span>
                        <span class="meta-chip">👤 {{ $survey->offering->teacher->name }}</span>
                        <span class="meta-chip">{{ $survey->questions_count }} question(s)</span>
                        @if ($survey->end_date)
                            <span class="deadline-chip {{ $deadlineCls }}">
                                @if ($daysLeft <= 0)
                                    Closes today
                                @elseif ($daysLeft === 1)
                                    Closes tomorrow
                                @else
                                    Closes {{ $survey->end_date->format('M d, Y') }}
                                @endif
                            </span>
                        @endif
                        @if ($isUrgent)
                            <span style="color:#dc2626;font-weight:600;font-size:.72rem;">⚠ Urgent</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('survey.take', $survey->id) }}" class="btn-take">Take Survey →</a>
            </div>
            @endforeach
        @endif

        {{-- ── Enrolled Courses ── --}}
        <div class="section-header" style="margin-top:1.5rem;">
            <h2>📚 My Enrolled Courses
                <span style="font-size:.78rem;font-weight:400;color:#6b7280;margin-left:.4rem;">
                    {{ $viewAll ? 'All Semesters' : ($activeSemester?->full_label ?? 'Active Semester') }}
                </span>
            </h2>
            @if ($activeSemester)
                <a href="{{ route('student.dashboard', ['all_semesters' => ! $viewAll]) }}"
                   class="toggle-btn">
                    {{ $viewAll ? 'Show Active Semester' : 'View All Semesters' }}
                </a>
            @endif
        </div>

        @if ($enrollments->isEmpty())
            <div class="card">
                <div class="empty">
                    <div class="big">📭</div>
                    <p>
                        No enrollments found
                        {{ $viewAll ? '' : 'for the active semester' }}.
                        @if (! $viewAll && $activeSemester)
                            <a href="{{ route('student.enrollments.index') }}" style="color:#059669;">Enroll in a course →</a>
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Faculty</th>
                            <th>Block / Group</th>
                            <th>Type</th>
                            @if ($viewAll)<th>Semester</th>@endif
                            <th>Survey Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                        @php
                            // Check if there's a pending survey for this offering
                            $offeringSurvey = $pendingSurveys->firstWhere('offering_id', $enrollment->offering_id);
                            $hasPending     = $offeringSurvey !== null;
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $enrollment->offering->subject->course_code }}</div>
                                <div style="font-size:.75rem;color:#6b7280;">{{ $enrollment->offering->subject->name }}</div>
                            </td>
                            <td style="font-size:.82rem;">{{ $enrollment->offering->teacher->name }}</td>
                            <td style="font-size:.78rem;">
                                @if ($enrollment->offering->block)
                                    <span class="badge badge-block">{{ $enrollment->offering->block->name }}</span>
                                @endif
                                @if ($enrollment->offering->group_number)
                                    <span style="color:#6b7280;">Grp {{ $enrollment->offering->group_number }}</span>
                                @endif
                                @if (! $enrollment->offering->block && ! $enrollment->offering->group_number)
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($enrollment->enrollmentType)
                                    <span class="badge {{ $enrollment->enrollmentType->name === 'Block-Enrolled' ? 'badge-block' : 'badge-indiv' }}">
                                        {{ $enrollment->enrollmentType->name }}
                                    </span>
                                @endif
                            </td>
                            @if ($viewAll)
                                <td style="font-size:.78rem;color:#6b7280;">{{ $enrollment->offering->semester->full_label }}</td>
                            @endif
                            <td>
                                @if ($hasPending)
                                    <a href="{{ route('survey.take', $offeringSurvey->id) }}"
                                       style="display:inline-flex;align-items:center;gap:.3rem;background:#fef3c7;color:#92400e;padding:.2rem .55rem;border-radius:4px;font-size:.72rem;font-weight:600;text-decoration:none;">
                                        ⏳ Pending
                                    </a>
                                @else
                                    <span style="color:#9ca3af;font-size:.78rem;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Completed Surveys ── --}}
        @if ($completedAttempts->isNotEmpty())
        <div class="section-header" style="margin-top:.5rem;">
            <h2>✅ Recently Completed Surveys</h2>
            <a href="{{ route('survey.index') }}">View All →</a>
        </div>
        <div class="card">
            <table>
                <thead>
                    <tr><th>Survey</th><th>Course</th><th>Semester</th><th>Submitted</th></tr>
                </thead>
                <tbody>
                    @foreach ($completedAttempts as $attempt)
                    <tr>
                        <td style="font-size:.82rem;">{{ Str::limit($attempt->survey->title, 50) }}</td>
                        <td style="font-size:.78rem;color:#6b7280;">{{ $attempt->survey->offering->subject->course_code }}</td>
                        <td style="font-size:.78rem;color:#6b7280;">{{ $attempt->survey->offering->semester->full_label }}</td>
                        <td style="font-size:.78rem;color:#6b7280;">{{ $attempt->submitted_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard — CQI System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; color: #111; display: flex; min-height: 100vh; font-size: 14px; }

        /* ── Sidebar ── */
        .sidebar { width: 220px; background: #1e3a5f; color: #bfdbfe; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 1.25rem 1rem; border-bottom: 1px solid #1e40af; }
        .sidebar-brand h2 { font-size: 1rem; font-weight: 700; color: #fff; }
        .sidebar-brand span { font-size: .72rem; color: #93c5fd; }
        .sidebar-nav { flex: 1; padding: .5rem 0; }
        .nav-section { font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; color: #60a5fa; padding: .75rem 1rem .25rem; }
        .nav-link { display: flex; align-items: center; gap: .6rem; padding: .5rem 1rem; font-size: .83rem; color: #bfdbfe; text-decoration: none; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: #1e40af; color: #fff; }
        .nav-link .icon { width: 18px; text-align: center; font-size: .95rem; }
        .sidebar-user { padding: .85rem 1rem; border-top: 1px solid #1e40af; }
        .sidebar-user .name { font-size: .82rem; font-weight: 600; color: #fff; }
        .sidebar-user .role { font-size: .72rem; color: #93c5fd; }
        .btn-logout { margin-top: .5rem; background: none; border: 1px solid #3b82f6; color: #93c5fd; padding: .3rem .75rem; border-radius: 5px; font-size: .75rem; cursor: pointer; width: 100%; }
        .btn-logout:hover { background: #1e40af; color: #fff; }

        /* ── Main ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: #fff; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .topbar-left h1 { font-size: 1.1rem; font-weight: 700; color: #1e3a5f; }
        .topbar-left p  { font-size: .8rem; color: #6b7280; margin-top: .1rem; }
        .topbar-right   { font-size: .82rem; color: #6b7280; }
        .semester-badge { background: #dbeafe; color: #1d4ed8; padding: .2rem .65rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }

        .content { flex: 1; padding: 1.5rem; overflow-y: auto; }

        /* ── Stat cards ── */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: #fff; border-radius: 10px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-icon.blue   { background: #dbeafe; }
        .stat-icon.green  { background: #d1fae5; }
        .stat-icon.purple { background: #ede9fe; }
        .stat-icon.orange { background: #ffedd5; }
        .stat-val   { font-size: 1.6rem; font-weight: 700; line-height: 1; color: #111; }
        .stat-label { font-size: .75rem; color: #6b7280; margin-top: .2rem; }

        /* ── Section headers ── */
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: .75rem; }
        .section-header h2 { font-size: .95rem; font-weight: 700; color: #1e3a5f; }
        .section-header a  { font-size: .8rem; color: #3b82f6; text-decoration: none; }

        /* ── Grid layouts ── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }

        /* ── Cards ── */
        .card { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
        .card-head { padding: .65rem 1rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: .82rem; color: #374151; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 1rem; }
        .card-body.no-pad { padding: 0; }

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

        /* ── Analytics preview ── */
        .score-bar-wrap { margin-bottom: .6rem; }
        .score-bar-label { display: flex; justify-content: space-between; font-size: .78rem; color: #374151; margin-bottom: .2rem; }
        .score-bar-track { height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
        .score-bar-fill  { height: 100%; border-radius: 999px; transition: width .6s ease; }
        .score-bar-fill.good { background: #059669; }
        .score-bar-fill.fair { background: #d97706; }
        .score-bar-fill.low  { background: #dc2626; }

        /* Sentiment ring */
        .sentiment-row { display: flex; gap: .5rem; margin-top: .75rem; }
        .sentiment-pill { flex: 1; padding: .5rem; border-radius: 8px; text-align: center; }
        .sentiment-pill .pct { font-size: 1.2rem; font-weight: 700; }
        .sentiment-pill .lbl { font-size: .7rem; margin-top: .1rem; }
        .s-pos { background: #f0fdf4; color: #065f46; }
        .s-neu { background: #fefce8; color: #854d0e; }
        .s-neg { background: #fef2f2; color: #b91c1c; }

        /* ── Survey status mini cards ── */
        .survey-mini { padding: .75rem 1rem; border-bottom: 1px solid #f3f4f6; }
        .survey-mini:last-child { border-bottom: none; }
        .survey-mini-title { font-weight: 600; font-size: .83rem; color: #111; margin-bottom: .2rem; }
        .survey-mini-meta  { font-size: .75rem; color: #6b7280; display: flex; gap: .75rem; flex-wrap: wrap; }

        /* ── Empty state ── */
        .empty { text-align: center; padding: 2rem; color: #9ca3af; font-size: .85rem; }

        /* ── Alert ── */
        .alert { padding: .65rem 1rem; border-radius: 7px; font-size: .82rem; margin-bottom: 1rem; }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-warn    { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }

        /* ── CQI report row ── */
        .report-row { display: flex; justify-content: space-between; align-items: center; padding: .65rem 1rem; border-bottom: 1px solid #f3f4f6; }
        .report-row:last-child { border-bottom: none; }
        .report-title { font-size: .82rem; font-weight: 500; color: #111; }
        .report-meta  { font-size: .72rem; color: #9ca3af; margin-top: .1rem; }
        .btn-sm { padding: .25rem .65rem; border-radius: 5px; font-size: .75rem; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; background: #fff; white-space: nowrap; }
        .btn-sm:hover { background: #f3f4f6; }
        .btn-primary-sm { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .btn-primary-sm:hover { background: #1e40af; }
    </style>
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>CQI System</h2>
        <span>Faculty Portal</span>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Main</p>
        <a href="{{ route('faculty.dashboard') }}" class="nav-link active">
            <span class="icon">🏠</span> Dashboard
        </a>
        <a href="{{ route('survey.index') }}" class="nav-link">
            <span class="icon">📋</span> My Surveys
        </a>
        <a href="{{ route('faculty.reports.index') }}" class="nav-link">
            <span class="icon">📊</span> My CQI Reports
        </a>
    </nav>
    <div class="sidebar-user">
        <div class="name">{{ $user->name }}</div>
        <div class="role">{{ $user->user_id_number }} · Faculty</div>
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
            <h1>Welcome back, {{ explode(' ', $user->name)[0] }} 👋</h1>
            <p>Here's an overview of your courses and evaluations.</p>
        </div>
        <div class="topbar-right">
            @if ($activeSemester)
                <span class="semester-badge">{{ $activeSemester->full_label }}</span>
            @else
                <span style="color:#9ca3af;">No active semester</span>
            @endif
        </div>
    </div>

    <div class="content">

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (! $activeSemester)
            <div class="alert alert-warn">No active semester is currently set. Contact the administrator.</div>
        @endif

        {{-- ── Stat Cards ── --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div>
                    <div class="stat-val">{{ $activeOfferings->count() }}</div>
                    <div class="stat-label">Active Courses</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">👥</div>
                <div>
                    <div class="stat-val">{{ $activeOfferings->sum('enrollments_count') }}</div>
                    <div class="stat-label">Students This Semester</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">📝</div>
                <div>
                    <div class="stat-val">{{ $activeSurveys->count() }}</div>
                    <div class="stat-label">Active Surveys</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">⭐</div>
                <div>
                    <div class="stat-val">{{ $overallAvgRating ? number_format($overallAvgRating, 2) : '—' }}</div>
                    <div class="stat-label">Overall Avg Rating</div>
                </div>
            </div>
        </div>

        {{-- ── Row: Course Offerings + Survey Status ── --}}
        <div class="two-col">

            {{-- Course Offerings --}}
            <div class="card">
                <div class="card-head">
                    📚 My Courses This Semester
                    <span style="font-size:.75rem;color:#6b7280;font-weight:400;">{{ $activeOfferings->count() }} offering(s)</span>
                </div>
                @if ($activeOfferings->isEmpty())
                    <p class="empty">No courses assigned this semester.</p>
                @else
                    <div class="card-body no-pad">
                        <table>
                            <thead>
                                <tr><th>Subject</th><th>Block/Group</th><th>Students</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($activeOfferings as $offering)
                                <tr>
                                    <td>
                                        <div style="font-weight:600;">{{ $offering->subject->course_code }}</div>
                                        <div style="font-size:.75rem;color:#6b7280;">{{ $offering->subject->name }}</div>
                                    </td>
                                    <td style="font-size:.78rem;color:#6b7280;">
                                        {{ $offering->block?->name ?? '—' }}
                                        @if ($offering->group_number)
                                            <span style="margin-left:.3rem;">Grp {{ $offering->group_number }}</span>
                                        @endif
                                    </td>
                                    <td style="font-weight:600;">{{ $offering->enrollments_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Survey Status --}}
            <div class="card">
                <div class="card-head">
                    📋 Survey Status
                    <a href="{{ route('survey.index') }}">View All →</a>
                </div>
                @if ($activeSurveys->isEmpty() && $inactiveSurveys->isEmpty())
                    <p class="empty">No surveys for your courses yet.</p>
                @else
                    @foreach ($activeSurveys->take(4) as $survey)
                    <div class="survey-mini">
                        <div class="survey-mini-title">
                            {{ $survey->offering->subject->course_code }}
                            — {{ Str::limit($survey->title, 45) }}
                        </div>
                        <div class="survey-mini-meta">
                            <span class="badge badge-active">Active</span>
                            <span>{{ $survey->attempts_count }} response(s)</span>
                            @if ($survey->end_date)
                                <span>Closes {{ $survey->end_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @foreach ($inactiveSurveys->take(3) as $survey)
                    <div class="survey-mini">
                        <div class="survey-mini-title">
                            {{ $survey->offering->subject->course_code }}
                            — {{ Str::limit($survey->title, 45) }}
                        </div>
                        <div class="survey-mini-meta">
                            <span class="badge badge-inactive">Inactive</span>
                            <span>{{ $survey->attempts_count }} response(s)</span>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ── Analytics Preview ── --}}
        @if ($analyticsRecords->isNotEmpty())
        <div class="section-header">
            <h2>📊 Analytics Preview — {{ $activeSemester?->full_label ?? 'This Semester' }}</h2>
            <a href="{{ route('faculty.reports.index') }}">View CQI Reports →</a>
        </div>

        <div class="two-col">

            {{-- Category Scores ── --}}
            <div class="card">
                <div class="card-head">Average Scores by Category</div>
                <div class="card-body">
                    @if (empty($avgCategoryScores))
                        <p class="empty" style="padding:1rem;">No category data yet.</p>
                    @else
                        @php
                            $scaleMax = 5; // adjust if using different scale
                        @endphp
                        @foreach ($avgCategoryScores as $cat => $score)
                        @php
                            $pct      = min(100, round(($score / $scaleMax) * 100));
                            $fillCls  = $pct >= 80 ? 'good' : ($pct >= 60 ? 'fair' : 'low');
                            $interp   = $pct >= 90 ? 'Excellent' : ($pct >= 80 ? 'Very Good' : ($pct >= 70 ? 'Good' : ($pct >= 60 ? 'Fair' : 'Needs Improvement')));
                        @endphp
                        <div class="score-bar-wrap">
                            <div class="score-bar-label">
                                <span>{{ $cat }}</span>
                                <span style="font-weight:600;color:{{ $fillCls === 'good' ? '#065f46' : ($fillCls === 'fair' ? '#92400e' : '#b91c1c') }}">
                                    {{ number_format($score, 2) }} / {{ $scaleMax }}
                                    <span style="font-weight:400;color:#9ca3af;">({{ $interp }})</span>
                                </span>
                            </div>
                            <div class="score-bar-track">
                                <div class="score-bar-fill {{ $fillCls }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Sentiment + Response count ── --}}
            <div class="card">
                <div class="card-head">Open-ended Sentiment Summary</div>
                <div class="card-body">

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                        <div>
                            <div style="font-size:1.8rem;font-weight:700;color:#1e3a5f;">{{ number_format($totalResponses) }}</div>
                            <div style="font-size:.75rem;color:#6b7280;">Total Responses</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:1.8rem;font-weight:700;color:#059669;">
                                {{ $overallAvgRating ? number_format($overallAvgRating, 2) : '—' }}
                            </div>
                            <div style="font-size:.75rem;color:#6b7280;">Overall Avg Rating</div>
                        </div>
                    </div>

                    @if ($avgPositivePct !== null)
                    <div class="sentiment-row">
                        <div class="sentiment-pill s-pos">
                            <div class="pct">{{ number_format($avgPositivePct, 1) }}%</div>
                            <div class="lbl">Positive</div>
                        </div>
                        <div class="sentiment-pill s-neu">
                            <div class="pct">{{ number_format($avgNeutralPct ?? 0, 1) }}%</div>
                            <div class="lbl">Neutral</div>
                        </div>
                        <div class="sentiment-pill s-neg">
                            <div class="pct">{{ number_format($avgNegativePct ?? 0, 1) }}%</div>
                            <div class="lbl">Negative</div>
                        </div>
                    </div>
                    @else
                        <p style="color:#9ca3af;font-size:.82rem;margin-top:.5rem;">
                            Sentiment data not yet available. It is computed after surveys are closed.
                        </p>
                    @endif

                    {{-- Per-survey breakdown ── --}}
                    @if ($analyticsRecords->count() > 1)
                    <div style="margin-top:1rem;border-top:1px solid #f3f4f6;padding-top:.75rem;">
                        <p style="font-size:.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Per Survey</p>
                        @foreach ($analyticsRecords->take(4) as $rec)
                        <div style="display:flex;justify-content:space-between;font-size:.78rem;padding:.25rem 0;border-bottom:1px solid #f9fafb;">
                            <span style="color:#374151;">{{ $rec->survey->offering->subject->course_code }}</span>
                            <span style="font-weight:600;color:{{ ($rec->avg_rating ?? 0) >= 3.5 ? '#065f46' : '#92400e' }}">
                                {{ $rec->avg_rating ? number_format($rec->avg_rating, 2) : '—' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ── CQI Reports ── --}}
        @if ($cqiReports->isNotEmpty())
        <div class="section-header">
            <h2>📄 Recent CQI Reports</h2>
            <a href="{{ route('faculty.reports.index') }}">View All →</a>
        </div>
        <div class="card" style="margin-bottom:1.5rem;">
            @foreach ($cqiReports as $report)
            <div class="report-row">
                <div>
                    <div class="report-title">{{ $report->title }}</div>
                    <div class="report-meta">
                        {{ $report->survey?->offering?->semester?->full_label }} ·
                        Generated {{ $report->created_at->format('M d, Y') }}
                        @if ($report->is_regenerated)
                            · <span style="color:#d97706;">Regenerated</span>
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:.4rem;">
                    <a href="{{ route('faculty.reports.show', $report->id) }}" class="btn-sm">View</a>
                    <a href="{{ route('faculty.reports.download', $report->id) }}" class="btn-sm btn-primary-sm">↓ PDF</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── All-time Summary ── --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-head">📈 All-time Summary</div>
            <div class="card-body">
                <div style="display:flex;gap:2.5rem;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:1.6rem;font-weight:700;color:#1e3a5f;">{{ $totalOfferings }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">Total Courses Handled</div>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:700;color:#059669;">{{ $totalStudentsTaught }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">Unique Students Taught</div>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:700;color:#7c3aed;">{{ $cqiReports->count() }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">CQI Reports Generated</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>

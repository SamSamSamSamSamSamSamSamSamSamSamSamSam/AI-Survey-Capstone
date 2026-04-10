@extends('admin.layouts.app')
@section('title', 'Faculty Analytics')

@section('content')
<div class="page-header">
    <h1>Faculty Analytics</h1>
</div>

<form method="GET" action="{{ route('admin.analytics.index') }}">
    <div class="filters">
        <select name="semester_id" class="form-control" style="min-width:220px;">
            <option value="">All Semesters</option>
            @foreach ($semesters as $sem)
                <option value="{{ $sem->id }}" @selected($selectedSemesterId == $sem->id)>
                    {{ $sem->full_label }} {{ $sem->is_active ? '(Active)' : '' }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

<div class="card">
    @if ($analytics->isEmpty())
        <p class="empty-state">No analytics computed yet. Analytics are generated automatically when a survey is deactivated.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Faculty</th>
                    <th>Course</th>
                    <th>Semester</th>
                    <th>Responses</th>
                    <th>Avg Rating</th>
                    <th>Sentiment</th>
                    <th>Last Computed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics as $analytic)
                <tr>
                    <td style="font-weight:500;">{{ $analytic->faculty->name }}</td>
                    <td style="font-size:.82rem;">
                        {{ $analytic->survey->offering->subject->course_code }}<br>
                        <span style="color:#6b7280;">{{ $analytic->survey->offering->subject->name }}</span>
                    </td>
                    <td style="font-size:.8rem;">{{ $analytic->survey->offering->semester->full_label }}</td>
                    <td>{{ $analytic->response_count }}</td>
                    <td>
                        @if ($analytic->avg_rating)
                            <span style="font-weight:600;color:{{ $analytic->avg_rating >= 3.5 ? '#065f46' : '#92400e' }}">
                                {{ number_format($analytic->avg_rating, 2) }}
                            </span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;">
                        @if ($analytic->positive_sentiment_percent !== null)
                            <span style="color:#065f46;">▲ {{ number_format($analytic->positive_sentiment_percent, 1) }}%</span>
                            <span style="color:#6b7280;margin:0 .25rem;">·</span>
                            <span style="color:#b91c1c;">▼ {{ number_format($analytic->negative_sentiment_percent, 1) }}%</span>
                        @else
                            <span style="color:#9ca3af;">No text responses</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;color:#6b7280;">
                        {{ $analytic->last_computed_at?->format('M d, Y h:i A') ?? '—' }}
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.analytics.show', $analytic->id) }}" class="btn btn-sm btn-secondary">View</a>
                            <form method="POST" action="{{ route('admin.analytics.recompute', $analytic->survey_id) }}">
                                @csrf
                                <button class="btn btn-sm btn-warning">Recompute</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $analytics->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

@extends('admin.layouts.app')
@section('title', $offering->display_name)

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offerings.index') }}">Course Offerings</a></li>
    <li class="breadcrumb-item active">View</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1>{{ $offering->display_name }}</h1>
    <div class="actions">
        <a href="{{ route('admin.offerings.edit', $offering->id) }}" class="btn btn-secondary">Edit</a>
        <a href="{{ route('admin.offerings.enrollments.index', $offering->id) }}" class="btn btn-warning">Manage Enrollments</a>
        <a href="{{ route('admin.offerings.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
    <div class="card">
        <div class="card-body">
            <p class="form-text" style="margin-bottom:.5rem;">OFFERING DETAILS</p>
            <table style="font-size:.875rem;">
                <tr><td style="color:#6b7280;padding:.35rem .5rem .35rem 0;width:140px;">Subject</td><td>{{ $offering->subject->course_code }} — {{ $offering->subject->name }}</td></tr>
                <tr><td style="color:#6b7280;padding:.35rem .5rem .35rem 0;">Semester</td><td>{{ $offering->semester->full_label }}</td></tr>
                <tr><td style="color:#6b7280;padding:.35rem .5rem .35rem 0;">Faculty</td><td>{{ $offering->teacher->name }}</td></tr>
                <tr><td style="color:#6b7280;padding:.35rem .5rem .35rem 0;">Type</td><td>{{ $offering->offeringType?->name ?? '—' }}</td></tr>
                <tr><td style="color:#6b7280;padding:.35rem .5rem .35rem 0;">Group</td><td>{{ $offering->group_number ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="form-text" style="margin-bottom:.5rem;">ENROLLMENT SUMMARY</p>
            <div style="font-size:2rem;font-weight:700;color:#4f46e5;">{{ $offering->enrollments->count() }}</div>
            <div style="font-size:.85rem;color:#6b7280;">Total Students Enrolled</div>
        </div>
    </div>
</div>

{{-- Enrolled Students --}}
<div class="card">
    <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;display:flex;justify-content:space-between;align-items:center;">
        <span>Enrolled Students</span>
        <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}" class="btn btn-sm btn-primary">+ Enroll Student</a>
    </div>
    @if ($offering->enrollments->isEmpty())
        <p class="empty-state">No students enrolled yet.</p>
    @else
        <table>
            <thead>
                <tr><th>ID Number</th><th>Name</th><th>Status</th><th>Enrolled On</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($offering->enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->student->user_id_number }}</td>
                    <td>{{ $enrollment->student->name }}</td>
                    <td><span class="badge badge-active" style="color: black">{{ ucfirst($enrollment->enrollmentType->name) }}</span></td>
                    <td style="font-size:.8rem;">{{ $enrollment->created_at->format('M d, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.offerings.enrollments.destroy', [$offering->id, $enrollment->id]) }}" onsubmit="return confirm('Remove this student from the offering?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

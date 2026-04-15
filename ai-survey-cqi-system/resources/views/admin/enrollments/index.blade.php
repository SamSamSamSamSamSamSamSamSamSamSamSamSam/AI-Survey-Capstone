@extends('layouts.app')
@section('title', 'Enrollments')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Enrollments</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Enrollments — {{ $offering->subject->course_code }}</h1>
    <div class="actions">
        <a href="{{ route('admin.offerings.enrollments.create', $offering->id) }}" class="btn btn-primary">+ Enroll Student</a>
        <a href="{{ route('admin.offerings.show', $offering->id) }}" class="btn btn-secondary">← Back to Offering</a>
    </div>
</div>

<div class="alert alert-info" style="font-size:.875rem;">
    <strong>{{ $offering->subject->name }}</strong> &nbsp;·&nbsp;
    {{ $offering->semester->full_label }} &nbsp;·&nbsp;
    Faculty: {{ $offering->teacher->name }}
</div>

<div class="card">
    @if ($enrollments->isEmpty())
        <p class="empty-state">No students enrolled yet.</p>
    @else
        <table>
            <thead>
                <tr><th>ID Number</th><th>Name</th><th>Email</th><th>Status</th><th>Enrolled On</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->student->user_id_number }}</td>
                    <td>{{ $enrollment->student->name }}</td>
                    <td style="font-size:.8rem;">{{ $enrollment->student->email }}</td>
                    <td><span class="badge badge-active" style="color: black">{{ ucfirst($enrollment->enrollmentType->name) }}</span></td>
                    <td style="font-size:.8rem;">{{ $enrollment->created_at->format('M d, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.offerings.enrollments.destroy', [$offering->id, $enrollment->id]) }}" onsubmit="return confirm('Remove this student?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $enrollments->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

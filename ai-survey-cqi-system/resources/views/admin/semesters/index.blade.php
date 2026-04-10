@extends('admin.layouts.app')
@section('title', 'Semesters')

@section('content')
<div class="page-header">
    <h1>Semesters</h1>
    <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary">+ New Semester</a>
</div>

@php $activeSemester = \App\Models\Semester::current(); @endphp

@if ($activeSemester)
    <div class="alert alert-info" style="margin-bottom:1.25rem;">
        Active semester: <strong>{{ $activeSemester->full_label }}</strong>
    </div>
@else
    <div class="alert" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;margin-bottom:1.25rem;">
        No active semester is currently set. Course offerings and student enrollment are unavailable until a semester is activated.
    </div>
@endif

<div class="card">
    @if ($semesters->isEmpty())
        <p class="empty-state">No semesters yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Sem #</th>
                    <th>A.Y. Start</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($semesters as $semester)
                <tr>
                    <td><strong>{{ $semester->full_label }}</strong></td>
                    <td>{{ $semester->semester_number }}</td>
                    <td>{{ $semester->academic_start_year }}-{{ $semester->academic_start_year + 1 }}</td>
                    <td>
                        @if ($semester->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.semesters.edit', $semester->id) }}" class="btn btn-sm btn-secondary">Edit</a>

                            @if (! $semester->is_active)
                                <form method="POST" action="{{ route('admin.semesters.activate', $semester->id) }}" onsubmit="return confirm('Set this as the active semester?')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success">Activate</button>
                                </form>
                                <form method="POST" action="{{ route('admin.semesters.destroy', $semester->id) }}" onsubmit="return confirm('Delete this semester?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.semesters.deactivate', $semester->id) }}" onsubmit="return confirm('Deactivate this semester? No active semester will be set.')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-warning">Deactivate</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $semesters->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

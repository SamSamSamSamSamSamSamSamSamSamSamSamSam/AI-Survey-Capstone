@extends('admin.layouts.app')
@section('title', 'Survey Templates')

@section('content')
<div class="page-header">
    <h1>Survey Templates</h1>
    <a href="{{ route('admin.survey-templates.create') }}" class="btn btn-primary">+ New Template</a>
</div>

<div class="card">
    @if ($templates->isEmpty())
        <p class="empty-state">No templates yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Questions</th>
                    <th>Official</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($templates as $template)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $template->name }}</div>
                        @if ($template->description)
                            <div style="font-size:.78rem;color:#6b7280;">{{ Str::limit($template->description, 80) }}</div>
                        @endif
                    </td>
                    <td>{{ $template->questions_count }}</td>
                    <td>
                        @if ($template->is_official)
                            <span class="badge" style="background:#fef3c7;color:#92400e;">⭐ Official</span>
                        @else
                            <span style="color:#9ca3af;font-size:.82rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($template->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.survey-templates.show', $template->id) }}" class="btn btn-sm btn-secondary">Manage</a>
                            <a href="{{ route('admin.survey-templates.edit', $template->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                            @if (! $template->is_official)
                                <form method="POST" action="{{ route('admin.survey-templates.destroy', $template->id) }}" onsubmit="return confirm('Delete this template?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $templates->links('pagination::simple-tailwind') }}</div>
    @endif
</div>
@endsection

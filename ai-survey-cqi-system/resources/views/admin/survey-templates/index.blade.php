@extends('layouts.app')
@section('title', 'Survey Templates')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Survey Templates</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Survey Templates</h2>
        <p class="page-subheading">Manage reusable question sets for survey creation.</p>
    </div>
    <a href="{{ route('admin.survey-templates.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Template
    </a>
</div>

<div class="card">
    @if ($templates->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-layout-text-sidebar"></i></div>
            <p class="empty-state-text">No templates yet.</p>
            <a href="{{ route('admin.survey-templates.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Template
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th class="text-center">Questions</th>
                        <th>Official</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="template-icon">
                                    <i class="bi bi-layout-text-sidebar"></i>
                                </div>
                                <div>
                                    <div class="fw-500">{{ $template->name }}</div>
                                    @if ($template->description)
                                        <div class="text-muted-sm">
                                            {{ Str::limit($template->description, 80) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="count-badge">{{ $template->questions_count }}</span>
                        </td>

                        <td>
                            @if ($template->is_official)
                                <span class="official-badge">
                                    <i class="bi bi-star-fill me-1"></i>Official
                                </span>
                            @else
                                <span class="text-muted-sm">—</span>
                            @endif
                        </td>

                        <td>
                            @if ($template->is_active)
                                <span class="status-pill status-pill--active">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @else
                                <span class="status-pill status-pill--inactive">
                                    <i class="bi bi-pause-circle me-1"></i>Inactive
                                </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="table-actions">
                                <a href="{{ route('admin.survey-templates.show', $template->id) }}"
                                   class="btn btn-sm btn-icon" title="Manage Questions">
                                    <i class="bi bi-list-check"></i>
                                </a>
                                <a href="{{ route('admin.survey-templates.edit', $template->id) }}"
                                   class="btn btn-sm btn-icon" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if (! $template->is_official)
                                    <form method="POST"
                                          action="{{ route('admin.survey-templates.destroy', $template->id) }}"
                                          class="d-inline"
                                          data-confirm="Delete the template &quot;{{ $template->name }}&quot;? This cannot be undone.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($templates->hasPages())
            <div class="table-pagination">
                {{ $templates->links() }}
            </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/confirm-action.js') }}"></script>
@endpush
@extends('layouts.app')
@section('title', 'Question Categories')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Question Categories</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Question Categories</h2>
        <p class="page-subheading">Organize questions into categories for better survey management.</p>
    </div>
    <a href="{{ route('admin.question-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Category
    </a>

</div>

<div class="card">
    @if ($categories->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-tags"></i></div>
            <p class="empty-state-text">No categories yet.</p>
            <a href="{{ route('admin.question-categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Category
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table data-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Used In Questions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $cat)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="category-icon">
                                    <i class="bi bi-tags"></i>
                                </div>
                                <div>
                                    <div class="fw-500">{{ $cat->name }}</div>
                                    
                                </div>
                            </div>
                        </td>
                        <td>{{ $cat->description ?? '—' }}</td>
                        <td class="align-middle">
                            <div class="usage-container">

                                {{-- Total --}}
                                <span class="total-count">
                                    {{ $cat->survey_questions_count + $cat->template_questions_count }}
                                </span>

                                {{-- Pill + Labels --}}
                                <div class="pill-wrapper">
                                    <div class="split-pill">
                                        <span class="pill-left">
                                            {{ $cat->survey_questions_count }}
                                        </span>
                                        <span class="pill-right">
                                            {{ $cat->template_questions_count }}
                                        </span>
                                    </div>

                                    <div class="pill-labels muted">
                                        <span class="label-left">Survey</span>
                                        <span class="label-right">Template</span>
                                    </div>
                                </div>

                            </div>
                        </td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="{{ route('admin.question-categories.edit', $cat->id) }}" 
                                    class="btn btn-sm btn-icon" title="Edit Categoty">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.question-categories.destroy', $cat->id) }}"
                                    class="d-inline"
                                    data-confirm="Delete the category &quot;{{ $cat->name }}&quot;? This will not delete the questions using this category, but will remove the category association from them."  
                                    >
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-icon btn-icon--danger" title="Delete Category">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $categories->links('pagination::simple-tailwind') }}</div>

    @endif

</div>





{{-- <div class="page-header">
    <h1>Question Categories</h1>
    <a href="{{ route('admin.question-categories.create') }}" class="btn btn-primary">+ New Category</a>
</div>
<div class="card">
    @if ($categories->isEmpty())
        <p class="empty-state">No categories yet.</p>
    @else
        <table>
            <thead>
                <tr><th>Name</th><th>Description</th><th>Used In Questions</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($categories as $cat)
                <tr>
                    <td style="font-weight:500;">{{ $cat->name }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $cat->description ?? '—' }}</td>
                    <td style="font-size:.82rem;">
                        {{ $cat->survey_questions_count + $cat->template_questions_count }}
                        <span style="color:#9ca3af;">({{ $cat->survey_questions_count }} survey, {{ $cat->template_questions_count }} template)</span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.question-categories.edit', $cat->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.question-categories.destroy', $cat->id) }}"
                                  onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $categories->links('pagination::simple-tailwind') }}</div>
    @endif
</div>--}}
@endsection 

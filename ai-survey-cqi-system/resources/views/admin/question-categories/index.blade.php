{{-- resources/views/admin/question-categories/index.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Question Categories')
@section('content')
<div class="page-header">
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
</div>
@endsection

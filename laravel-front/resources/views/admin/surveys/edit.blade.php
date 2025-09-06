@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Edit Survey: <span class="text-primary">{{ $survey->title }}</span></h2>
        <a href="{{ route('admin.surveys.index', $survey->id) }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Survey
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <h6 class="mb-2"><i class="fa fa-exclamation-circle me-1"></i> Please fix the following errors:</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            {{-- The form uses the 'update' route with the PUT method --}}
            <form action="{{ route('admin.surveys.update', $survey->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Survey Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Survey Title *</label>
                    <input type="text" name="title" id="title" 
                           class="form-control" placeholder="Enter survey title" 
                           value="{{ old('title', $survey->title) }}" required>
                </div>

                {{-- Survey Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Survey Description</label>
                    <textarea name="description" id="description" rows="3" 
                              class="form-control" placeholder="Enter survey description (optional)">{{ old('description', $survey->description) }}</textarea>
                </div>

                {{-- Target Role --}}
                <div class="mb-3">
                    <label for="target_role" class="form-label fw-semibold">Target Audience *</label>
                    <select name="target_role" id="target_role" class="form-select" required>
                        <option value="">-- Select Target Audience --</option>
                        <option value="admin" {{ old('target_role', $survey->target_role) == 'admin' ? 'selected' : '' }}>Administrators</option>
                        <option value="teacher" {{ old('target_role', $survey->target_role) == 'teacher' ? 'selected' : '' }}>Teachers</option>
                        <option value="student" {{ old('target_role', $survey->target_role) == 'student' ? 'selected' : '' }}>Students</option>
                        <option value="both" {{ old('target_role', $survey->target_role) == 'both' ? 'selected' : '' }}>Both Teachers and Students</option>
                    </select>
                </div>

                {{-- Question Selector --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Add Questions</label>
                    <select id="questionType" class="form-select">
                        <option value="">-- Select Question Type to Add --</option>
                        <option value="rating">Rating Scale (1–5)</option>
                        <option value="text">Open-ended</option>
                    </select>
                </div>

                {{-- Dynamic Questions --}}
                <div id="questionsContainer" class="mb-3">
                    {{-- Pre-populate with existing questions from the survey model --}}
                    @foreach(old('questions', $survey->questions->pluck('question_text')->toArray()) as $question)
                        <div class="card p-3 mb-2 shadow-sm question-card">
                            <label class="fw-semibold">Question</label>
                            <input type="text" name="questions[]" class="form-control mb-2" 
                                   value="{{ $question }}" placeholder="Enter your question" required>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update Survey
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script for dynamic question handling --}}
<script>
document.getElementById('questionType').addEventListener('change', function () {
    let container = document.getElementById('questionsContainer');
    let type = this.value;

    if (type === 'rating') {
        let card = document.createElement('div');
        card.classList.add('card', 'p-3', 'mb-2', 'shadow-sm', 'question-card');
        card.innerHTML = `
            <label class="fw-semibold">Rating Question</label>
            <input type="text" name="questions[]" class="form-control mb-2" placeholder="Enter your question (e.g., Rate the instructor's knowledge on a scale of 1-5)" required>
            <small class="text-muted">Scale: 1 = Strongly Disagree, 5 = Strongly Agree</small>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        `;
        container.appendChild(card);
    } else if (type === 'text') {
        let card = document.createElement('div');
        card.classList.add('card', 'p-3', 'mb-2', 'shadow-sm', 'question-card');
        card.innerHTML = `
            <label class="fw-semibold">Open-ended Question</label>
            <input type="text" name="questions[]" class="form-control mb-2" placeholder="Enter your question" required>
            <div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        `;
        container.appendChild(card);
    }
    this.value = ""; // reset dropdown
});

// Remove Question Event
document.getElementById('questionsContainer').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-question') || e.target.closest('.remove-question')) {
        e.target.closest('.question-card').remove();
    }
});

// Validate at least one question exists before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const questionCards = document.querySelectorAll('.question-card');
    if (questionCards.length === 0) {
        e.preventDefault();
        alert('Please add at least one question to the survey.');
    }
});
</script>
@endsection
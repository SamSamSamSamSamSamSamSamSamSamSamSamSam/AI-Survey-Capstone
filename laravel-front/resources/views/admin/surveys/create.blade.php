@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Create New Survey</h2>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Surveys
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
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
        <div class="alert alert-success">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.surveys.store') }}" method="POST">
                @csrf

                {{-- Survey Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Survey Title</label>
                    <input type="text" name="title" id="title" 
                           class="form-control" placeholder="Enter survey title" required>
                </div>

                {{-- Survey Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Survey Description</label>
                    <textarea name="description" id="description" rows="3" 
                              class="form-control" placeholder="Enter survey description (optional)"></textarea>
                </div>

                {{-- Question Selector --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Add Question</label>
                    <select id="questionType" class="form-select">
                        <option value="">-- Select Question Type --</option>
                        <option value="likert">Likert Scale (1–5)</option>
                        <option value="open">Open-ended</option>
                    </select>
                </div>

                {{-- Dynamic Questions --}}
                <div id="questionsContainer" class="mb-3"></div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Save Survey
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

    if (type === 'likert') {
        let card = document.createElement('div');
        card.classList.add('card', 'p-3', 'mb-2', 'shadow-sm');
        card.innerHTML = `
            <label class="fw-semibold">Likert Question</label>
            <input type="text" name="questions[]" class="form-control mb-2" placeholder="Enter your question" required>
            <small class="text-muted">Scale: 1 = Strongly Disagree, 5 = Strongly Agree</small>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        `;
        container.appendChild(card);
    } else if (type === 'open') {
        let card = document.createElement('div');
        card.classList.add('card', 'p-3', 'mb-2', 'shadow-sm');
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
        e.target.closest('.card').remove();
    }
});
</script>
@endsection

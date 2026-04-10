@extends('layouts.default')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">

            {{-- Back Button --}}
            <button id="back-button" class="btn btn-outline-secondary mb-4">
                <i class="bi bi-arrow-left me-2"></i> Go Back
            </button>

            {{-- Survey Header --}}
            <div class="card shadow-lg mb-5 border-0 bg-primary-subtle text-primary-emphasis">
                <div class="card-body p-4 text-center">
                    <h1 class="card-title fw-bolder mb-3">{{ $survey->title }}</h1>
                    <p class="card-text fs-5 text-muted border-top pt-3">{{ $survey->description }}</p>
                    <h6 class="mt-3"><strong>Evaluatee:</strong> {{ $survey->evaluatee->name }}</h6>
                    <h6><strong>Course:</strong> {{ $survey->subject->course_code ?? 'N/A' }} (Group {{ $survey->group ?? 'N/A' }})</h6>
                </div>
            </div>

            {{-- Survey Form --}}
            <form id="survey-form" action="{{ route('student.surveys.submit', $survey->id) }}" method="POST">
                @csrf
                <input type="hidden" name="evaluatee_id" value="{{ $survey->evaluatee_id }}">
                <input type="hidden" name="subject_id" value="{{ $survey->subject->id ?? '' }}">

                @php
                    $groupedQuestions = $survey->questions->groupBy(fn($q) => $q->category ?? 'Uncategorized');
                @endphp

                @foreach ($groupedQuestions as $categoryName => $questions)
                    <div class="card shadow-sm mb-5 border-0">
                        <div class="card-header bg-primary text-white fw-bold fs-5">{{ $categoryName }}</div>
                        <div class="card-body bg-light">
                            @foreach ($questions as $question)
                                <div class="mb-4 p-3 bg-white rounded shadow-sm">
                                    <label class="form-label fs-6 fw-semibold mb-3">
                                        <span class="badge bg-primary me-2">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</span>
                                        {{ $question->question_text }}
                                    </label>

                                    @if ($question->type === 'rating')
                                        <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-light rounded border">
                                            <span class="text-start text-muted small me-3">Strongly Disagree / Low</span>
                                            <div class="d-flex gap-3">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <div class="form-check form-check-inline">
                                                        <input 
                                                            type="radio" 
                                                            name="responses[{{ $question->id }}]" 
                                                            id="q{{ $question->id }}_{{ $i }}" 
                                                            value="{{ $i }}" 
                                                            class="form-check-input"
                                                            required
                                                        >
                                                        <label class="form-check-label fw-bold text-dark" for="q{{ $question->id }}_{{ $i }}">
                                                            {{ $i }}
                                                        </label>
                                                    </div>
                                                @endfor
                                            </div>
                                            <span class="text-end text-muted small ms-3">Strongly Agree / High</span>
                                        </div>
                                    @else
                                        <textarea 
                                            name="responses[{{ $question->id }}]" 
                                            class="form-control mt-3" 
                                            rows="4" 
                                            placeholder="Enter your detailed response here..." 
                                            required
                                        ></textarea>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="text-center mt-5">
                    <button id="submit-button" type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                        <i class="bi bi-check-circle me-2"></i> Submit Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('survey-form');
    const submitButton = document.getElementById('submit-button');
    let formModified = false;

    // Track changes
    form.querySelectorAll('input, textarea').forEach(el => {
        el.addEventListener('change', () => formModified = true);
    });

    // Warn if leaving page
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Back button confirmation
    document.getElementById('back-button').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('WARNING: Leaving this page will discard your answers. Continue?')) {
            window.history.back();
        }
    });

    // Submit confirmation + prevent double submit
    form.addEventListener('submit', function(e) {
        // Check all required fields
        const requiredInputs = form.querySelectorAll('input[required], textarea[required]');
        for (const input of requiredInputs) {
            if (!input.value) {
                alert('Please answer all questions before submitting.');
                input.focus();
                e.preventDefault();
                return false;
            }
        }

        if (!confirm('Are you sure you want to submit your evaluation?')) {
            e.preventDefault();
            return false;
        }

        // Prevent beforeunload from firing after submission
        formModified = false;

        // Disable submit to prevent double submission
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
        
        submitting = true;
    });
});

</script>
@endsection

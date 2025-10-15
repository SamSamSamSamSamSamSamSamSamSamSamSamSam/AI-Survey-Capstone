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
                <div class="card-body p-4 p-md-5">
                    <h1 class="card-title fw-bolder mb-3 text-center">
                        {{ $survey->title }}
                    </h1>
                    <p class="card-text fs-5 text-center text-muted border-top pt-3">
                        {{ $survey->description }}
                    </p>
                    <h7><strong>Evaluatee:</strong> {{ $survey->evaluatee->name }}</h7><br>
                    <h7><strong>Course:</strong> {{ $survey->subject->name ?? 'N/A' }} (Group {{ $survey->group ?? 'N/A' }})</h7>
                </div>
            </div>
            
            <form action="{{ route('student.surveys.submit', $survey->id) }}" method="POST">
                @csrf
                    <input type="hidden" name="evaluatee_id" value="{{ $survey->evaluatee_id }}">
                    <input type="hidden" name="subject_id" value="{{ $subject->id ?? '' }}">

                @foreach ($survey->questions as $index => $question)
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            
                            <label class="form-label fs-5 mb-3">
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span> 
                                <span class="fw-bold">{{ $question->question_text }}</span>
                            </label>

                            @if ($question->type === 'rating')
                                <div class="rating-scale d-flex justify-content-between align-items-center mt-3 p-3 bg-light rounded border">
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
                    </div>
                @endforeach

                <div class="text-center mt-5">
                    <button id= submit-button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                        <i class="bi bi-check-circle me-2"></i> Submit Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript for Confirmation --}}
<script>
    document.getElementById('back-button').addEventListener('click', function(e) {
        // Prevent default action (if it were an anchor tag)
        e.preventDefault(); 
        
        const confirmGoBack = confirm('WARNING: If you leave this page, all unsaved progress on your evaluation will be lost. Are you sure you want to go back?');
        
        if (confirmGoBack) {
            // Use the browser's history to go to the previous page
            window.history.back();
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const submitButton = document.getElementById('submit-button');
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                const confirmed = confirm('Are you sure you want to submit this evaluation?');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        }
    });

</script>

@endsection
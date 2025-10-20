@extends('layouts.default')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/surveys/faculty.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Edit Survey: <span class="text-primary">{{ $survey->title }}</span></h2>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to Surveys
                </a>
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm mb-4">
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
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.surveys.update', $survey->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Survey Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Survey Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control form-control-sm" 
                                   placeholder="Enter survey title" value="{{ old('title', $survey->title) }}" required>
                        </div>

                        {{-- Survey Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Survey Description</label>
                            <textarea name="description" id="description" class="form-control form-control-sm" rows="3"
                                      placeholder="Enter survey description (optional)">{{ old('description', $survey->description) }}</textarea>
                        </div>

                        <div class="row">
                            {{-- Person to Evaluate --}}
                            <div class="col-md-6 mb-3">
                                <label for="evaluatee_id" class="form-label fw-semibold">Person to Evaluate <span class="text-danger">*</span></label>
                                <select name="evaluatee_id" id="evaluatee_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Faculty or Admin --</option>
                                    @foreach($evaluatees as $member)
                                        <option value="{{ $member->id }}" 
                                            {{ old('evaluatee_id', $survey->evaluatee_id) == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Target Audience --}}
                            <div class="col-md-6 mb-3">
                                <label for="target_role" class="form-label fw-semibold">Target Audience <span class="text-danger">*</span></label>
                                <select name="target_role" id="target_role" class="form-select form-select-sm" required>
                                    <option value="">-- Select Audience --</option>
                                    <option value="admin" {{ old('target_role', $survey->target_role) == 'admin' ? 'selected' : '' }}>Administrators</option>
                                    <option value="teacher" {{ old('target_role', $survey->target_role) == 'teacher' ? 'selected' : '' }}>Teachers</option>
                                    <option value="student" {{ old('target_role', $survey->target_role) == 'student' ? 'selected' : '' }}>Students</option>
                                    <option value="both" {{ old('target_role', $survey->target_role) == 'both' ? 'selected' : '' }}>Both Teachers and Students</option>
                                </select>
                            </div>

                            {{-- Subject / Course --}}
                            <div class="col-md-6 mb-3" id="subject-field-container">
                                <label for="subject_id" class="form-label fw-semibold" id="subject-label">Course</label>
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm"
                                        data-selected="{{ old('subject_id', $survey->subject_id) }}">
                                    <option value="">-- Select Course --</option>
                                </select>
                            </div>

                            <input type="hidden" name="group" id="group_field" value="{{ old('group', $survey->group) }}">
                        </div>

                        <hr class="my-4">

                        {{-- Add Questions --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-semibold">Add Questions</h6>
                            <select id="questionType" class="form-select form-select-sm w-auto">
                                <option value="">Add Question Type</option>
                                <option value="rating">Rating Scale (1–5)</option>
                                <option value="text">Open-ended</option>
                            </select>
                        </div>

                        {{-- Existing / Dynamic Questions --}}
                        <div id="questionsContainer" class="mb-3">
                            @foreach($survey->questions as $q)
                                <div class="card p-3 mb-3 shadow-sm question-card">
                                    @if($q->type === 'rating')
                                        <label class="fw-semibold">Rating Question</label>
                                        <input type="hidden" name="question_types[]" value="rating">
                                        <input type="text" name="questions[]" class="form-control mb-2" value="{{ $q->question_text }}" required>
                                        <div class="mt-2 small text-muted">
                                            Scale preview:
                                            <div>
                                                @for($i=1; $i<=5; $i++)
                                                    <label class="me-2"><input type="radio" disabled> {{ $i }}</label>
                                                @endfor
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-question">
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                    @else
                                        <label class="fw-semibold">Open-ended Question</label>
                                        <input type="hidden" name="question_types[]" value="text">
                                        <input type="text" name="questions[]" class="form-control mb-2" value="{{ $q->question_text }}" required>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Update Survey
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/surveys/edit.js') }}"></script>
@endpush

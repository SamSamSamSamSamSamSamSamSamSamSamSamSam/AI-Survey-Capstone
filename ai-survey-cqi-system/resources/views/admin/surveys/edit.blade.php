@extends('layouts.default')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/surveys/edit.css') }}">
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

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.surveys.update', $survey->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Survey Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Survey Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control form-control-sm"
                                   value="{{ old('title', $survey->title) }}" required>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="description" class="form-control form-control-sm" rows="3">{{ old('description', $survey->description) }}</textarea>
                        </div>

                        <div class="row">
                            {{-- Evaluatee --}}
                            <div class="col-md-6 mb-3">
                                <label for="evaluatee_id" class="form-label fw-semibold">Person to Evaluate <span class="text-danger">*</span></label>
                                <select name="evaluatee_id" id="evaluatee_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Faculty or Admin --</option>
                                    @foreach($evaluatees as $member)
                                        <option value="{{ $member->id }}" {{ old('evaluatee_id', $survey->evaluatee_id) == $member->id ? 'selected' : '' }}>
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
                                    @foreach(['admin','teacher','student','both'] as $role)
                                        <option value="{{ $role }}" {{ old('target_role', $survey->target_role) == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Subject --}}
                            <div class="col-md-6 mb-3" id="subject-field-container">
                                <label for="subject_id" class="form-label fw-semibold" id="subject-label">Course</label>
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm" data-selected="{{ old('subject_id', $survey->subject_id) }}">
                                    <option value="">-- Select Course --</option>
                                </select>
                            </div>

                            <input type="hidden" name="group" id="group_field" value="{{ old('group', $survey->group) }}">
                        </div>

                        <hr class="my-4">

                        {{-- Add Category --}}
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <label for="categorySelect" class="form-label fw-semibold mb-0">Select Category:</label>
                            <select id="categorySelect" class="form-select form-select-sm w-auto">
                                <option value="">-- Select Category --</option>
                                <option value="Classroom Management">Classroom Management</option>
                                <option value="Teaching and Learning">Teaching and Learning</option>
                                <option value="Assessment">Assessment</option>
                                <option value="General Open-Ended Questions">General Open-Ended Questions</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addCategoryBtn">
                                <i class="fa fa-plus me-1"></i> Add Category
                            </button>
                        </div>

                        {{-- Categories Container --}}
                        <div id="selectedCategories" class="mb-3">
                            @php $groupedQuestions = $survey->questions->groupBy('category'); @endphp
                            @foreach($groupedQuestions as $category => $questions)
                                <div class="card-category mb-3" data-category="{{ $category }}">
                                    <div class="card-category-header">
                                        <span>{{ $category }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-category">Remove</button>
                                    </div>
                                    <div class="card-category-body">
                                        <div class="mb-2">
                                            <label class="form-label fw-semibold">Add Question:</label>
                                            <select class="form-select form-select-sm questionTypeSelect">
                                                <option value="">Select Type</option>
                                                <option value="rating">Rating (1–5)</option>
                                                <option value="text">Open-ended</option>
                                            </select>
                                        </div>
                                        <div class="category-questions">
                                            @foreach($questions as $q)
                                                <div class="card-question">
                                                    <input type="hidden" name="question_types[{{ $category }}][]" value="{{ $q->type }}">
                                                    <input type="text" name="questions[{{ $category }}][]" class="form-control form-control-sm mb-2" value="{{ $q->question_text }}" required>
                                                    @if($q->type === 'rating')
                                                        <div class="small text-muted mb-1">
                                                            Scale Preview: @for($i=1;$i<=5;$i++)<label class="me-2"><input type="radio" disabled> {{ $i }}</label>@endfor
                                                        </div>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-question">Remove</button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary me-2"><i class="fa fa-arrow-left me-1"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Update Survey</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin/surveys/edit.js') }}"></script>
@endpush

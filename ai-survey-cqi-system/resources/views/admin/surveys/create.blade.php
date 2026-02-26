@extends('layouts.default')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/surveys/create.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create New Survey</h5>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6 class="fw-semibold mb-2"><i class="fa fa-exclamation-circle me-1"></i> Please correct the following errors:</h6>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.surveys.store') }}" method="POST">
                        @csrf

                        {{-- Survey Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Survey Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control form-control-sm"
                                   placeholder="Enter survey title" value="{{ old('title') }}" required>
                        </div>

                        {{-- Survey Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="description" class="form-control form-control-sm" rows="3"
                                      placeholder="Enter survey description (optional)">{{ old('description') }}</textarea>
                        </div>


                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="evaluatee_id" class="form-label fw-semibold">Person to Evaluate <span class="text-danger">*</span></label>
                                <select name="evaluatee_id" id="evaluatee_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Faculty or Admin --</option>
                                    @foreach($faculty as $member)
                                        <option value="{{ $member->id }}" {{ old('evaluatee_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="target_role" class="form-label fw-semibold">Target Audience <span class="text-danger">*</span></label>
                                <select name="target_role" id="target_role" class="form-select form-select-sm" required>
                                    <option value="">-- Select Audience --</option>
                                    <option value="admin" {{ old('target_role') == 'admin' ? 'selected' : '' }}>Administrators</option>
                                    <option value="teacher" {{ old('target_role') == 'teacher' ? 'selected' : '' }}>Teachers</option>
                                    <option value="student" {{ old('target_role') == 'student' ? 'selected' : '' }}>Students</option>
                                    <option value="both" {{ old('target_role') == 'both' ? 'selected' : '' }}>Both Teachers and Students</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="subject-field-container">
                                <label for="subject_id" class="form-label fw-semibold" id="subject-label">Course</label>
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm">
                                    <option value="">-- Select Course --</option>
                                </select>
                            </div>

                            <input type="hidden" name="group" id="group_field">
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <button type="button" id="useOfficialBtn" class="btn btn-success btn-sm">
                                <i class="fa fa-file-alt me-1"></i> Use Official University Questionnaire
                            </button>
                        </div>

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
                        <div id="selectedCategories" class="mb-3"></div>

                        {{-- Questions Container --}}
                        <div id="questionsContainer"></div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-save me-1"></i> Save Survey
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
    <script src="{{ asset('js/admin/surveys/create.js') }}"></script>
@endpush
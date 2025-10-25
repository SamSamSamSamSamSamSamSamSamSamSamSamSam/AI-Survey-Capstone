@extends('layouts.default')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/surveys/faculty.css') }}">
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
                            {{-- Person to Evaluate --}}
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
                            
                            {{-- Target Audience --}}
                            {{-- MOVED this field up to control the subject field --}}
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
                                {{-- MODIFIED: Added id="subject-label" --}}
                                <label for="subject_id" class="form-label fw-semibold" id="subject-label">Course</label>
                                {{-- MODIFIED: Removed 'required' attribute --}}
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm">
                                    <option value="">-- Select Course --</option>
                                </select>
                            </div>

                            <input type="hidden" name="group" id="group_field">
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

                        {{-- Dynamic Questions --}}
                        <div id="questionsContainer" class="mb-3"></div>

                        <div class="text-end">
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
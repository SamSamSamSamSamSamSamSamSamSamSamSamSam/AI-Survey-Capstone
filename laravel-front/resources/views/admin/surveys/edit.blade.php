@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Edit Survey: <span class="text-primary">{{ $survey->title }}</span></h2>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Surveys
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

                    {{-- Target Audience (MOVED UP) --}}
                    <div class="col-md-6 mb-3">
                        <label for="target_role" class="form-label fw-semibold">Target Audience <span class="text-danger">*</span></label>
                        <select name="target_role" id="target_role" class="form-select" required>
                            <option value="">-- Select Target Audience --</option>
                            <option value="admin" {{ old('target_role', $survey->target_role) == 'admin' ? 'selected' : '' }}>Administrators</option>
                            <option value="teacher" {{ old('target_role', $survey->target_role) == 'teacher' ? 'selected' : '' }}>Teachers</option>
                            <option value="student" {{ old('target_role', $survey->target_role) == 'student' ? 'selected' : '' }}>Students</option>
                            <option value="both" {{ old('target_role', $survey->target_role) == 'both' ? 'selected' : '' }}>Both Teachers and Students</option>
                        </select>
                    </div>

                    {{-- Subject (MODIFIED) --}}
                    <div class="col-md-6 mb-3" id="subject-field-container">
                        <label for="subject_id" class="form-label fw-semibold" id="subject-label">Subject</label>
                        {{-- Removed 'required' attribute --}}
                        <select name="subject_id" id="subject_id" class="form-select form-select-sm">
                            <option value="">-- Select Subject --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject['id'] }}" 
                                        data-group="{{ $subject['group'] }}"
                                        {{ $survey->subject_id == $subject['id'] ? 'selected' : '' }}>
                                    {{ $subject['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="group" id="group_field" value="{{ old('group', $survey->group) }}">
                </div>

                {{-- Add Question Section --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Add Question</label>
                    <select id="questionType" class="form-select">
                        <option value="">-- Select Question Type --</option>
                        <option value="rating">Rating Scale (1–5)</option>
                        <option value="text">Open-ended</option>
                    </select>
                </div>

                {{-- Dynamic Questions --}}
                <div id="questionsContainer" class="mb-3">
                    {{-- Existing questions --}}
                    @foreach($survey->questions as $q)
                        <div class="card p-3 mb-3 shadow-sm question-card">
                            @if($q->type === 'rating')
                                <label class="fw-semibold">Rating Question</label>
                                <input type="hidden" name="question_types[]" value="rating">
                                <input type="text" name="questions[]" class="form-control mb-2" 
                                       value="{{ $q->question_text }}" required>
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
                                <input type="text" name="questions[]" class="form-control mb-2" 
                                       value="{{ $q->question_text }}" required>
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

{{-- Dynamic Question Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    const targetRoleSelect = document.getElementById('target_role');
    const subjectContainer = document.getElementById('subject-field-container');
    const subjectSelect = document.getElementById('subject_id');
    const subjectLabel = document.getElementById('subject-label');
    const groupField = document.getElementById('group_field');

    function toggleSubjectField() {
        const selectedRole = targetRoleSelect.value;
        
        if (selectedRole === 'teacher') {
            subjectContainer.style.display = 'none';
            subjectSelect.required = false;
            subjectSelect.disabled = true;
            subjectLabel.innerHTML = 'Subject';
        } else {
            subjectContainer.style.display = 'block';
            subjectSelect.required = true;
            subjectSelect.disabled = false;
            subjectLabel.innerHTML = 'Subject <span class="text-danger">*</span>'; // Add red asterisk
        }
    }


    targetRoleSelect.addEventListener('change', toggleSubjectField);
    
    toggleSubjectField();


    document.getElementById('questionType').addEventListener('change', function () {
        const container = document.getElementById('questionsContainer');
        const type = this.value;
        if (!type) return;

        const card = document.createElement('div');
        card.classList.add('card', 'p-3', 'mb-3', 'shadow-sm', 'question-card');

        if (type === 'rating') {
            card.innerHTML = `
                <label class="fw-semibold">Rating Question</label>
                <input type="hidden" name="question_types[]" value="rating">
                <input type="text" name="questions[]" class="form-control mb-2" placeholder="e.g., Rate the instructor’s clarity (1-5)" required>
                <div class="mt-2 small text-muted">
                    Scale preview:
                    <div>
                        ${[1,2,3,4,5].map(n => `<label class='me-2'><input type='radio' disabled> ${n}</label>`).join('')}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            `;
        } else if (type === 'text') {
            card.innerHTML = `
                <label class="fw-semibold">Open-ended Question</label>
                <input type="hidden" name="question_types[]" value="text">
                <input type="text" name="questions[]" class="form-control mb-2" placeholder="e.g., What did you like most about this course?" required>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            `;
        }

        container.appendChild(card);
        this.value = '';
    });

    document.getElementById('questionsContainer').addEventListener('click', function(e) {
        if (e.target.closest('.remove-question')) {
            e.target.closest('.question-card').remove();
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const questionCards = document.querySelectorAll('.question-card');
        if (questionCards.length === 0) {
            e.preventDefault();
            alert('Please add at least one question.');
        }
    });

    document.getElementById('evaluatee_id').addEventListener('change', function () {
        const teacherId = this.value;
        const subjectSelect = document.getElementById('subject_id');
        subjectSelect.innerHTML = '<option value="">-- Loading subjects... --</option>';

        if (!teacherId) {
            subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
            return;
        }

        fetch(`/admin/teachers/${teacherId}/subjects`)
            .then(response => response.json())
            .then(subjects => {
                subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
                if (subjects.length === 0) {
                    subjectSelect.innerHTML = '<option value="">No subjects found for this teacher</option>';
                } else {
                    subjects.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.dataset.group = subject.group;
                        option.textContent = subject.name;
                        subjectSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching subjects:', error);
                subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
            });
    });

    document.getElementById('subject_id').addEventListener('change', function() {

        if (this.selectedIndex > 0) {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('group_field').value = selectedOption.dataset.group || '';
        } else {
            document.getElementById('group_field').value = ''; // Clear if "-- Select Subject --" is chosen
        }
    });


    const subjectSelect = document.getElementById('subject_id');
    if (subjectSelect.selectedIndex > 0) {
        document.getElementById('group_field').value = subjectSelect.options[subjectSelect.selectedIndex].dataset.group || '';
    }

}); 
</script>
@endsection
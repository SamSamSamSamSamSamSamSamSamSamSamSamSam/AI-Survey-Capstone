@extends('layouts.default')

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
                            
                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label fw-semibold">Course</label>
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Course --</option>
                                </select>
                            </div>
                            <input type="hidden" name="group" id="group_field">

                            {{-- Target Audience --}}
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

<style>
.question-card {
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}
.question-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
}
.remove-question {
    font-size: 0.8rem;
}
</style>

<script>
document.getElementById('questionType').addEventListener('change', function () {
    const container = document.getElementById('questionsContainer');
    const type = this.value;
    if (!type) return;

    const card = document.createElement('div');
    card.classList.add('question-card', 'p-3', 'mb-3');

    if (type === 'rating') {
        card.innerHTML = `
            <label class="form-label fw-semibold">Rating Question</label>
            <input type="hidden" name="question_types[]" value="rating">
            <input type="text" name="questions[]" class="form-control form-control-sm mb-2"
                   placeholder="e.g., Rate the instructor’s clarity (1-5)" required>
            <div class="small text-muted">
                <span>Scale Preview: </span>
                ${[1,2,3,4,5].map(n => `<label class="me-2"><input type="radio" disabled> ${n}</label>`).join('')}
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-question">
                <i class="fa fa-trash me-1"></i> Remove
            </button>
        `;
    } else if (type === 'text') {
        card.innerHTML = `
            <label class="form-label fw-semibold">Open-ended Question</label>
            <input type="hidden" name="question_types[]" value="text">
            <input type="text" name="questions[]" class="form-control form-control-sm mb-2"
                   placeholder="e.g., What did you like most about this course?" required>
            <button type="button" class="btn btn-outline-danger btn-sm remove-question">
                <i class="fa fa-trash me-1"></i> Remove
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
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('group_field').value = selectedOption.dataset.group || '';
});
</script>
@endsection

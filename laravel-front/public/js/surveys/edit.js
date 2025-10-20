document.addEventListener('DOMContentLoaded', function() {

    // === DOM Elements ===
    const targetRoleSelect = document.getElementById('target_role');
    const subjectContainer = document.getElementById('subject-field-container');
    const subjectSelect = document.getElementById('subject_id');
    const subjectLabel = document.getElementById('subject-label');
    const groupField = document.getElementById('group_field');
    const questionTypeSelect = document.getElementById('questionType');
    const questionsContainer = document.getElementById('questionsContainer');
    const evaluateeSelect = document.getElementById('evaluatee_id');
    const form = document.querySelector('form');

    // Blade-provided selected values
    const selectedSubject = subjectSelect.dataset.selected;
    const selectedTeacher = evaluateeSelect.value;

    // === Toggle subject field based on role ===
    function toggleSubjectField() {
        const selectedRole = targetRoleSelect.value;
        if (selectedRole === 'teacher') {
            subjectContainer.style.display = 'none';
            subjectSelect.required = false;
            subjectSelect.disabled = true;
            subjectLabel.innerHTML = 'Course';
            subjectSelect.value = '';
            groupField.value = '';
        } else {
            subjectContainer.style.display = 'block';
            subjectSelect.required = true;
            subjectSelect.disabled = false;
            subjectLabel.innerHTML = 'Course <span class="text-danger">*</span>';
        }
    }

    // === Add question card ===
    function addQuestionCard(type, questionText = '') {
        if (!type) return;

        const card = document.createElement('div');
        card.classList.add('card', 'p-3', 'mb-3', 'shadow-sm', 'question-card');

        if (type === 'rating') {
            card.innerHTML = `
                <label class="fw-semibold">Rating Question</label>
                <input type="hidden" name="question_types[]" value="rating">
                <input type="text" name="questions[]" class="form-control mb-2" placeholder="e.g., Rate the instructor’s clarity (1-5)" value="${questionText}" required>
                <div class="mt-2 small text-muted">
                    Scale preview:
                    <div>${[1,2,3,4,5].map(n => `<label class='me-2'><input type='radio' disabled> ${n}</label>`).join('')}</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            `;
        } else if (type === 'text') {
            card.innerHTML = `
                <label class="fw-semibold">Open-ended Question</label>
                <input type="hidden" name="question_types[]" value="text">
                <input type="text" name="questions[]" class="form-control mb-2" placeholder="e.g., What did you like most about this course?" value="${questionText}" required>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                    <i class="fa fa-trash"></i> Remove
                </button>
            `;
        }

        questionsContainer.appendChild(card);
    }

    // === Update group field ===
    function updateGroupField() {
        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        groupField.value = selectedOption?.dataset.group || '';
    }

    // === Load teacher's subjects dynamically ===
    function loadTeacherSubjects(teacherId) {
        subjectSelect.innerHTML = '<option value="">-- Loading courses... --</option>';

        if (!teacherId) {
            subjectSelect.innerHTML = '<option value="">-- Select Course --</option>';
            groupField.value = '';
            return;
        }

        fetch(`/admin/teachers/${teacherId}/subjects`)
            .then(res => res.json())
            .then(subjects => {
                subjectSelect.innerHTML = '<option value="">-- Select Course --</option>';

                subjects.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.id;
                    option.dataset.group = subject.group;
                    option.textContent = `${subject.group} - ${subject.course_code}`;

                    if (subject.id == selectedSubject) {
                        option.selected = true;
                        groupField.value = subject.group || '';
                    }

                    subjectSelect.appendChild(option);
                });

                updateGroupField();
            })
            .catch(err => {
                console.error('Error fetching subjects:', err);
                subjectSelect.innerHTML = '<option value="">Error loading courses</option>';
                groupField.value = '';
            });
    }

    // === Event listeners ===
    targetRoleSelect.addEventListener('change', toggleSubjectField);
    questionTypeSelect.addEventListener('change', function() {
        addQuestionCard(this.value);
        this.value = '';
    });
    questionsContainer.addEventListener('click', e => {
        const removeBtn = e.target.closest('.remove-question');
        if (removeBtn) removeBtn.closest('.question-card').remove();
    });
    form.addEventListener('submit', e => {
        if (!questionsContainer.querySelector('.question-card')) {
            e.preventDefault();
            alert('Please add at least one question.');
        }
    });
    evaluateeSelect.addEventListener('change', function() {
        loadTeacherSubjects(this.value);
    });
    subjectSelect.addEventListener('change', updateGroupField);

    // === Initial setup ===
    toggleSubjectField();
    if (selectedTeacher) {
        loadTeacherSubjects(selectedTeacher);
    }
    updateGroupField();
});

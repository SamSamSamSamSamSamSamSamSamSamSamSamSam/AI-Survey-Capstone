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
            subjectSelect.value = ''; 
            groupField.value = ''; 
            subjectLabel.innerHTML = 'Course'; 
        } else {

            subjectContainer.style.display = 'block';
            subjectSelect.required = true;
            subjectSelect.disabled = false;
            subjectLabel.innerHTML = 'Course <span class="text-danger">*</span>'; // Add red asterisk
        }
    }


    targetRoleSelect.addEventListener('change', toggleSubjectField);

    toggleSubjectField();


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

}); 
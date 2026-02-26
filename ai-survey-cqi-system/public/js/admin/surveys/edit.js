document.addEventListener('DOMContentLoaded', function() {
    const targetRoleSelect = document.getElementById('target_role');
    const subjectContainer = document.getElementById('subject-field-container');
    const subjectSelect = document.getElementById('subject_id');
    const subjectLabel = document.getElementById('subject-label');
    const groupField = document.getElementById('group_field');
    const evaluateeSelect = document.getElementById('evaluatee_id');
    const categorySelect = document.getElementById('categorySelect');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const selectedCategories = document.getElementById('selectedCategories');
    const questionsContainer = document.getElementById('questionsContainer');
    let categories = [];

    const selectedSubject = subjectSelect.dataset.selected;
    const selectedTeacher = evaluateeSelect.value;

    // Toggle subject field based on target role
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
            subjectLabel.innerHTML = 'Course <span class="text-danger">*</span>';
        }
    }

    targetRoleSelect.addEventListener('change', toggleSubjectField);
    toggleSubjectField();

    // Load teacher subjects and pre-select subject if editing
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
                subjects.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.dataset.group = sub.group;
                    opt.textContent = sub.name;
                    if(sub.id == selectedSubject) { 
                        opt.selected = true; 
                        groupField.value = sub.group;
                    }
                    subjectSelect.appendChild(opt);
                });
            })
            .catch(() => { 
                subjectSelect.innerHTML = '<option value="">Error loading courses</option>'; 
                groupField.value = '';
            });
    }

    if (selectedTeacher) loadTeacherSubjects(selectedTeacher);
    evaluateeSelect.addEventListener('change', function() {
        loadTeacherSubjects(this.value);
    });

    // Update group field when subject changes
    subjectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        groupField.value = selectedOption.dataset.group || '';
    });

    // CATEGORY + QUESTIONS LOGIC
    addCategoryBtn.addEventListener('click', () => {
        const category = categorySelect.value;
        if (!category) return alert('Please select a category');
        if (categories.includes(category)) return alert('Category already added');

        categories.push(category);

        const catCard = document.createElement('div');
        catCard.classList.add('card-category');
        catCard.dataset.category = category;

        catCard.innerHTML = `
            <div class="card-category-header">
                <span>${category}</span>
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
                <div class="category-questions"></div>
            </div>
        `;
        selectedCategories.appendChild(catCard);
    });

    // Remove category
    selectedCategories.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-category')){
            const card = e.target.closest('.card-category');
            const category = card.dataset.category;
            categories = categories.filter(c => c !== category);
            card.remove();
        }
    });

    // Add question inside category
    selectedCategories.addEventListener('change', function(e){
        if(!e.target.classList.contains('questionTypeSelect')) return;
        const type = e.target.value;
        if(!type) return;

        const card = e.target.closest('.card-category');
        const category = card.dataset.category;
        const questionDiv = card.querySelector('.category-questions');

        const qCard = document.createElement('div');
        qCard.classList.add('card-question');

        if(type === 'rating'){
            qCard.innerHTML = `
                <input type="hidden" name="question_types[${category}][]" value="rating">
                <input type="text" name="questions[${category}][]" class="form-control form-control-sm mb-2" placeholder="e.g., Rate clarity (1-5)" required>
                <div class="small text-muted mb-1">Scale Preview: ${[1,2,3,4,5].map(n=>`<label class="me-2"><input type="radio" disabled> ${n}</label>`).join('')}</div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-question">Remove</button>
            `;
        } else {
            qCard.innerHTML = `
                <input type="hidden" name="question_types[${category}][]" value="text">
                <input type="text" name="questions[${category}][]" class="form-control form-control-sm mb-2" placeholder="Enter open-ended question" required>
                <button type="button" class="btn btn-outline-danger btn-sm remove-question">Remove</button>
            `;
        }
        questionDiv.appendChild(qCard);
        e.target.value = '';
    });

    // Remove question
    selectedCategories.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-question')){
            e.target.closest('.card-question').remove();
        }
    });
});

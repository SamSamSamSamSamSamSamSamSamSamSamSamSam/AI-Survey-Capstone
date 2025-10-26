// public/js/student/survey_take.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const sidebarLinks = document.querySelectorAll('.fixed-sidebar a.nav-link');
    const logoutButton = document.querySelector('.btn-logout');
    let formChanged = false;

    // Detect if form has been modified
    form.addEventListener('input', () => {
        formChanged = true;
    });

    // Prevent clicking sidebar or logout without confirmation
    [...sidebarLinks, logoutButton].forEach(link => {
        link.addEventListener('click', function(e) {
            if (formChanged) {
                const confirmLeave = confirm(
                    '⚠️ You have unsaved answers. Leaving this page will discard your progress. Continue?'
                );
                if (!confirmLeave) {
                    e.preventDefault();
                }
            }
        });
    });

    // Warn before closing tab or reloading
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = ''; // Chrome requires this for confirmation dialog
        }
    });

    // Prevent submission unless all questions are answered
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let allAnswered = true;

        requiredFields.forEach(field => {
            if (field.type === 'radio') {
                const radios = form.querySelectorAll(`[name="${field.name}"]`);
                const isChecked = Array.from(radios).some(r => r.checked);
                if (!isChecked) allAnswered = false;
            } else if (!field.value.trim()) {
                allAnswered = false;
            }
        });

        if (!allAnswered) {
            e.preventDefault();
            alert('⚠️ Please answer all required questions before submitting.');
            return false;
        }

        const confirmSubmit = confirm('Are you sure you want to submit your evaluation?');
        if (!confirmSubmit) {
            e.preventDefault();
        } else {
            formChanged = false; // prevent confirmation after submission
        }
    });
});


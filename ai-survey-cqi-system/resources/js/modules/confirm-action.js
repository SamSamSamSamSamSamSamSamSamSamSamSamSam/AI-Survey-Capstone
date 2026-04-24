export function initConfirmAction() {
    const modalEl = document.getElementById('confirmActionModal');
    const modal = new bootstrap.Modal(modalEl);
    let formToSubmit = null;

    // Listen for form submissions
    document.addEventListener('submit', (e) => {
        const form = e.target;
        // Only trigger if the form has a data-confirm attribute
        if (!form.hasAttribute('data-confirm')) return;

        // If we haven't confirmed yet, stop the submission
        if (formToSubmit !== form) {
            e.preventDefault();
            formToSubmit = form;
            
            // Set message
            document.getElementById('confirmMessage').innerText = form.dataset.confirm;
            modal.show();
        }
    });

    // When "Confirm" is clicked in the modal
    document.getElementById('confirmBtn').addEventListener('click', () => {
        if (formToSubmit) {
            formToSubmit.submit(); // Now submit the actual form
        }
    });
    modalEl.addEventListener('hidden.bs.modal', () => {
        formToSubmit = null;
    });
}
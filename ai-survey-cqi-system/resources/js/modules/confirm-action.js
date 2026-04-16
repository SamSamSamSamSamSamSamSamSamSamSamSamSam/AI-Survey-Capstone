/**
 * confirm-action.js
 * A reusable utility for handling action confirmations.
 */

const ConfirmAction = {
    /**
     * Standard confirmation dialog
     * @param {string} message - The warning text
     * @returns {Promise<boolean>}
     */
    async ask(message = "Are you sure you want to proceed?") {
        // You can replace window.confirm with SweetAlert2 or a custom modal
        return window.confirm(message);
    },

    /**
     * Attaches confirmation to a form submission or button click
     * @param {string} selector - CSS selector for the element
     * @param {string} message - Custom message
     */
    init(selector = '.confirm-action', message) {
        const elements = document.querySelectorAll(selector);

        elements.forEach(element => {
            element.addEventListener('click', async (event) => {
                event.preventDefault();

                const customMessage = element.getAttribute('data-confirm') || message;
                const confirmed = await this.ask(customMessage);

                if (confirmed) {
                    // If it's a link, navigate. If it's a button in a form, submit.
                    if (element.tagName === 'A') {
                        window.location.href = element.href;
                    } else if (element.closest('form')) {
                        element.closest('form').submit();
                    }
                }
            });
        });
    }
};

// Auto-initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    ConfirmAction.init();
});

export default ConfirmAction;
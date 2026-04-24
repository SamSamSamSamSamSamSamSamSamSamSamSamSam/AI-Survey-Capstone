// ============================================================
// Loading Screen Manager
// Handles showing/hiding loading screens with proper timing
// ============================================================

class LoadingScreenManager {
    constructor() {
        this.loadingScreen = document.getElementById('loadingScreen');
        this.pageLoader = document.getElementById('pageLoader');
        this.hideTimeout = null;
        this.showTimeout = null;
    }

    /**
     * Show the full loading screen
     * @param {string} message - Custom message to display (optional)
     * @param {number} timeout - Auto-hide after ms (0 = manual, default = 0)
     */
    show(message = null, timeout = 0) {
        // Clear any pending timeouts
        clearTimeout(this.hideTimeout);
        clearTimeout(this.showTimeout);

        // Update message if provided
        if (message && this.loadingScreen) {
            const messageEl = this.loadingScreen.querySelector('.loading-message');
            if (messageEl) {
                messageEl.textContent = message;
            }
        }

        // Show immediately
        if (this.loadingScreen) {
            this.loadingScreen.classList.remove('hidden');
        }

        // Auto-hide if timeout specified
        if (timeout > 0) {
            this.hideTimeout = setTimeout(() => this.hide(), timeout);
        }
    }

    /**
     * Hide the loading screen
     * @param {number} delay - Delay before hiding in ms (default = 300)
     */
    hide(delay = 300) {
        clearTimeout(this.hideTimeout);
        clearTimeout(this.showTimeout);

        if (delay > 0) {
            this.hideTimeout = setTimeout(() => {
                if (this.loadingScreen) {
                    this.loadingScreen.classList.add('hidden');
                }
            }, delay);
        } else {
            if (this.loadingScreen) {
                this.loadingScreen.classList.add('hidden');
            }
        }
    }

    /**
     * Show page loader bar (for navigation/quick operations)
     */
    showPageLoader() {
        clearTimeout(this.showTimeout);
        if (this.pageLoader) {
            this.pageLoader.classList.remove('hidden');
        }
    }

    /**
     * Hide page loader bar
     */
    hidePageLoader() {
        clearTimeout(this.showTimeout);
        if (this.pageLoader) {
            this.pageLoader.classList.add('hidden');
        }
    }

    /**
     * Show temporary loading indicator (auto-hides after timeout)
     * @param {string} message - Message to display
     * @param {number} duration - Duration in ms (default = 2000)
     */
    showTemporary(message = 'Loading...', duration = 2000) {
        this.show(message);
        setTimeout(() => this.hide(), duration);
    }
}

// ============================================================
// GLOBAL INSTANCE
// ============================================================

const loader = new LoadingScreenManager();

// ============================================================
// AUTO HIDE ON PAGE LOAD
// ============================================================

// Hide loading screen when page fully loads
window.addEventListener('load', () => {
    loader.hide(500);
});

// Hide loading screen when DOM is interactive
document.addEventListener('DOMContentLoaded', () => {
    // Give a brief moment for initial render
    setTimeout(() => {
        if (document.readyState === 'loading') {
            loader.hidePageLoader();
        }
    }, 300);
});

// ============================================================
// FORM SUBMISSION HANDLING
// ============================================================

document.addEventListener('submit', (e) => {
    const form = e.target;
    
    // Skip if form has data-no-loading attribute
    if (form.hasAttribute('data-no-loading')) {
        return;
    }

    // Don't show loading for AJAX forms
    if (form.hasAttribute('data-ajax')) {
        return;
    }

    // Show page loader for regular form submissions
    loader.showPageLoader();
});

// ============================================================
// LINK NAVIGATION HANDLING
// ============================================================

document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    
    if (!link) return;

    // Skip if link has data-no-loading attribute
    if (link.hasAttribute('data-no-loading')) {
        return;
    }

    // Skip external links, anchors, and special links
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || link.target === '_blank') {
        return;
    }

    // Check if it's an external link
    if (link.hostname && link.hostname !== window.location.hostname) {
        return;
    }

    // Skip if link has download attribute
    if (link.hasAttribute('download')) {
        return;
    }

    // Show page loader for navigation
    loader.showPageLoader();
});

// ============================================================
// AJAX REQUEST HANDLING
// ============================================================

// Intercept fetch requests
const originalFetch = window.fetch;
window.fetch = function(...args) {
    const [resource, config] = args;
    const options = config || {};

    // Show loader unless explicitly disabled
    if (!options.disableLoading) {
        loader.showPageLoader();
    }

    return originalFetch.apply(this, args).finally(() => {
        loader.hidePageLoader();
    });
};

// ============================================================
// AXIOS INTERCEPTORS (if using axios)
// ============================================================

if (typeof window.axios !== 'undefined') {
    window.axios.interceptors.request.use(config => {
        if (!config.disableLoading) {
            loader.showPageLoader();
        }
        return config;
    });

    window.axios.interceptors.response.use(
        response => {
            loader.hidePageLoader();
            return response;
        },
        error => {
            loader.hidePageLoader();
            return Promise.reject(error);
        }
    );
}

// ============================================================
// EXPORT FOR GLOBAL USE
// ============================================================

if (typeof window !== 'undefined') {
    window.loader = loader;
}

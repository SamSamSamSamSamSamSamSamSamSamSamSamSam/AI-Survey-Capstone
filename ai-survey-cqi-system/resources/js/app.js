import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
window.Modal = bootstrap.Modal;

// Import only once at the top
import { initSessionTimeout } from './modules/session-timeout';
import './core/_theme';
import TomSelect from 'tom-select';
import Chart from 'chart.js/auto';

// Globalize vendors if needed elsewhere
window.TomSelect = TomSelect;
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Check if the modal exists in the HTML
    const modalExists = document.getElementById('sessionTimeoutModal');
    
    // 2. Check if the session config was passed from Blade
    const config = window.__cqiSession;

    if (modalExists && config) {
        console.log("Session Timeout System: Initializing...");
        initSessionTimeout(config);
    } else {
        console.warn("Session Timeout System: Modal or Config missing. Skipping.");
    }
});
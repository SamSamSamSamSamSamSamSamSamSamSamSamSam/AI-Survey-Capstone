import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
window.Modal = bootstrap.Modal;

import './core/_theme';
import TomSelect from 'tom-select';
import Chart from 'chart.js/auto';
import { initSessionMonitor } from './modules/session-timeout';
import { initConfirmAction } from './modules/confirm-action';

window.TomSelect = TomSelect;
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    // Pass the login route directly from the page or a global constant
    initSessionMonitor('/login'); 
    initConfirmAction();
});
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response.status === 419) {
            // Session expired: Force reload to redirect to login
            window.location.href = '/login?expired=1';
        }
        return Promise.reject(error);
    }
);
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
window.Modal = bootstrap.Modal;

import './core/_theme';
import TomSelect from 'tom-select';
import Chart from 'chart.js/auto';
import { initSessionTimeout } from './modules/session-timeout';

window.TomSelect = TomSelect;
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    const modalExists = document.getElementById('sessionTimeoutModal');
    const config = window.__cqiSession;

    if (modalExists && config) {
        initSessionTimeout(config);
    }
});
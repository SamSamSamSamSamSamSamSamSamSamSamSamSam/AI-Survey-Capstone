// auth.js — Authentication page interactions
(function () {
    'use strict';

    // ---- Theme persistence ----
    const THEME_KEY = 'cqi-theme';
    const html      = document.documentElement;
    const toggle    = document.getElementById('themeToggle');

    const saved = localStorage.getItem(THEME_KEY) || 'light';
    html.setAttribute('data-bs-theme', saved);
    if (toggle) toggle.checked = (saved === 'dark');

    if (toggle) {
        toggle.addEventListener('change', function () {
            const next = this.checked ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem(THEME_KEY, next);
        });
    }

    // ---- Password toggle ----
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const input    = document.getElementById(targetId);
            const icon     = this.querySelector('i');
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
                this.setAttribute('aria-label', 'Show password');
            }
        });
    });

    // ---- Login form submit loading state ----
    const loginForm = document.getElementById('loginForm');
    const loginBtn  = document.getElementById('loginBtn');

    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function () {
            const btnText    = loginBtn.querySelector('.btn-text');
            const btnLoading = loginBtn.querySelector('.btn-loading');

            loginBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
        });
    }

    // ---- Shake animation on error alert ----
    const errorAlert = document.querySelector('.alert-danger');
    if (errorAlert) {
        errorAlert.classList.add('auth-shake');
        errorAlert.addEventListener('animationend', function () {
            errorAlert.classList.remove('auth-shake');
        }, { once: true });
    }

    // ---- Entry animation: trigger on load ----
    const rightInner = document.querySelector('.auth-right-inner');
    if (rightInner) {
        rightInner.classList.add('auth-entry');
    }

    // ---- Feature card slideshow ----
    (function () {
        const slideshow = document.getElementById('authFeatureSlideshow');
        if (!slideshow) return;

        const slides = slideshow.querySelectorAll('.auth-feature-slide');
        const dots   = slideshow.querySelectorAll('.auth-feature-dot');
        if (!slides.length) return;

        let current = 0;
        let timer   = null;

        function goTo(index) {
            slides[current].classList.remove('is-active');
            dots[current] && dots[current].classList.remove('is-active');
            dots[current] && dots[current].setAttribute('aria-selected', 'false');

            current = (index + slides.length) % slides.length;

            slides[current].classList.add('is-active');
            dots[current] && dots[current].classList.add('is-active');
            dots[current] && dots[current].setAttribute('aria-selected', 'true');
        }

        function start() {
            timer = setInterval(function () { goTo(current + 1); }, 3500);
        }

        function stop() {
            clearInterval(timer);
            timer = null;
        }

        // Dot click navigation
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                stop();
                goTo(i);
                start();
            });
        });

        // Pause on hover
        slideshow.addEventListener('mouseenter', stop);
        slideshow.addEventListener('mouseleave', start);

        start();
    })();

})();

export function initSessionTimeout({ lifetimeMinutes, keepAliveUrl, loginUrl }) {
    console.log("Session Timeout module initialized.");

    const modalEl = document.getElementById('sessionTimeoutModal');
    const countdown = document.getElementById('session-countdown');
    const extendBtn = document.getElementById('extendSessionBtn');

    if (!modalEl || !extendBtn) return;

    const sessionLifetimeMs = lifetimeMinutes * 60 * 1000;
    const warningDurationMs = 60 * 1000;

    const triggerAfterMs = sessionLifetimeMs > warningDurationMs
        ? sessionLifetimeMs - warningDurationMs
        : Math.max(3000, sessionLifetimeMs * 0.5);

    function getModal() {
        return bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    }

    let warnTimer;
    let countdownInterval;

    function resetTimers() {
        clearTimeout(warnTimer);
        clearInterval(countdownInterval);

        warnTimer = setTimeout(() => {
            console.log("Session warning triggered.");
            const modal = getModal();
            modal.show();

            let secs = Math.round(warningDurationMs / 1000);
            if (countdown) countdown.innerText = secs;

            countdownInterval = setInterval(() => {
                secs--;
                if (countdown) countdown.innerText = Math.max(0, secs);
                if (secs <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = loginUrl;
                }
            }, 1000);
        }, triggerAfterMs);
    }

    // Reset on activity only if modal is hidden
    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, () => {
            if (!modalEl.classList.contains('show')) {
                resetTimers();
            }
        }, { passive: true });
    });

    // "Keep me signed in" logic
    extendBtn.addEventListener('click', () => {
        console.log("Extension requested...");
        fetch(keepAliveUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            if (r.ok) {
                console.log("Session successfully extended.");
                getModal().hide();
                resetTimers();
            } else {
                window.location.reload();
            }
        })
        .catch(() => window.location.reload());
    });

    resetTimers();
}
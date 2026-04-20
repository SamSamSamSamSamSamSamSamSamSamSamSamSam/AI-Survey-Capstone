export function initSessionTimeout({ lifetimeMinutes, keepAliveUrl, loginUrl }) {
    console.log("Session Timeout module initialized.");

    const modalEl = document.getElementById('sessionTimeoutModal');
    const countdown = document.getElementById('session-countdown');
    const extendBtn = document.getElementById('extendSessionBtn');

    if (!modalEl || !extendBtn) return;

    const sessionLifetimeMs = lifetimeMinutes * 60 * 1000;
    const warningDurationMs = 60 * 1000;

    // Trigger warning 1 minute before expiry, or at 50% if lifetime is very short
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
            method: 'GET',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            // If 401 or 419, the session is already dead on the server
            if (response.status === 401 || response.status === 419) {
                window.location.href = loginUrl;
                return;
            }

            if (response.ok) {
                console.log("Session successfully extended.");
                getModal().hide();
                resetTimers();
            } else {
                window.location.href = loginUrl;
            }
        })
        .catch(() => {
            // Network error usually means the session is invalid/expired
            window.location.href = loginUrl;
        });
    });

    resetTimers();
}
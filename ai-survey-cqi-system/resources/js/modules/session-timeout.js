export function initSessionMonitor(loginUrl) {
    const modal = new bootstrap.Modal(document.getElementById('sessionTimeoutModal'));

    // Check status every 60 seconds
    setInterval(async () => {
        const response = await fetch('/session/check');
        
        // If the server says we are unauthorized, the session is already dead
        if (response.status === 401 || response.status === 419) {
            window.location.href = loginUrl;
            return;
        }

        const data = await response.json();
        
        // Show modal if < 2 minutes left
        if (data.minutes_remaining <= 2) {
            modal.show();
        }
    }, 60000); 

    // Handle "Keep me signed in"
    document.getElementById('extendSessionBtn').addEventListener('click', async () => {
        await fetch('/session/refresh', { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
        modal.hide();
    });
}
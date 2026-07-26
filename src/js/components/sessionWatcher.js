export const bootSessionExpiryWatcher = (config = {}) => {
    const meta = document.querySelector('meta[name="session-expires-at"]');
    if (!(meta instanceof HTMLMetaElement)) return;
    const expiresAt = parseInt(meta.getAttribute('content') || '0', 10);
    if (!Number.isFinite(expiresAt) || expiresAt <= 0) return;

    const WARN_BEFORE_SECONDS = 60;
    let warned = false;
    const expiringMessage = config.expiringMessage || 'Token expires in ~{seconds}s. Save your work.';

    const tick = () => {
        const remaining = expiresAt - Math.floor(Date.now() / 1000);
        if (!warned && remaining > 0 && remaining <= WARN_BEFORE_SECONDS) {
            warned = true;
            const message = `[session] ${expiringMessage.replace('{seconds}', remaining)}`;
            console.warn(message);
            window.dispatchEvent(new CustomEvent('session:expiring-soon', { detail: { remainingSeconds: remaining, message } }));
        }
        if (remaining <= 0) {
            window.dispatchEvent(new CustomEvent('session:expired'));
            clearInterval(handle);
        }
    };

    const handle = setInterval(tick, 5000);
    tick();
};

const renderLucideIcons = () => {
    if (!window.lucide || typeof window.lucide.createIcons !== 'function') {
        return false;
    }
    window.requestAnimationFrame(() => {
        window.lucide.createIcons({ attrs: { 'stroke-width': 1.8 } });
    });
    return true;
};

export const bootLucideIcons = () => {
    if (renderLucideIcons()) return;
    let attempts = 0;
    const interval = setInterval(() => {
        attempts += 1;
        if (renderLucideIcons() || attempts >= 20) clearInterval(interval);
    }, 500);
};

export const cacheElapsed = (timestamp = '', labels = {}) => ({
    label: timestamp ? String(labels.calculating || 'Calculating...') : '',

    start() {
        if (!timestamp) return;

        const update = () => {
            const seconds = Math.max(0, Math.floor((Date.now() - Date.parse(timestamp)) / 1000));
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            const parts = [];

            if (days) parts.push(`${days}d`);
            if (hours || days) parts.push(`${hours}h`);
            if (minutes || hours || days) parts.push(`${minutes}m`);
            parts.push(`${secs}s`);
            this.label = `${String(labels.elapsedPrefix || 'Elapsed:')} ${parts.join(' ')}`;
        };

        update();
        window.setInterval(update, 1000);
    },
});

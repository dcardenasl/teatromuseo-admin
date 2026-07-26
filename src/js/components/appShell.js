export const appShell = () => ({
    sidebarOpen: window.innerWidth >= 768,

    init() {
        this.$watch('sidebarOpen', (isOpen) => {
            if (isOpen) this.queueScrollActiveSidebarItem();
        });
        this.queueScrollActiveSidebarItem();
    },

    queueScrollActiveSidebarItem() {
        if (window.innerWidth < 768 && !this.sidebarOpen) return;
        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => { this.scrollActiveSidebarItem(); });
        });
    },

    scrollActiveSidebarItem() {
        const sidebar = document.getElementById('app-sidebar');
        if (!sidebar) return;
        const scrollContainer = sidebar.querySelector('nav');
        const activeItem = sidebar.querySelector('a.bg-brand-50.text-brand-700');
        if (!scrollContainer || !activeItem) return;
        const containerRect = scrollContainer.getBoundingClientRect();
        const itemRect = activeItem.getBoundingClientRect();
        const padding = 24;
        const isOutOfView = itemRect.top < containerRect.top + padding || itemRect.bottom > containerRect.bottom - padding;
        if (!isOutOfView) return;
        const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
        activeItem.scrollIntoView({ block: 'center', inline: 'nearest', behavior });
    }
});

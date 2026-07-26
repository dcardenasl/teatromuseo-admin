import { uiLabels, localePrefix, focusableSelector } from '../utils/labels.js';

export const confirmStore = () => {
    const text = uiLabels[localePrefix()] || uiLabels.es;
    return {
        open: false,
        accepting: false,
        title: text.confirmAction,
        message: '',
        onAccept: null,
        show(message, onAccept, title = text.confirmAction) {
            this.open = true;
            this.accepting = false;
            this.message = message;
            this.title = title;
            this.onAccept = onAccept;
            requestAnimationFrame(() => {
                const dialog = document.getElementById('confirm-dialog-panel');
                if (!(dialog instanceof HTMLElement)) return;
                const focusable = dialog.querySelector(focusableSelector);
                if (focusable instanceof HTMLElement) { focusable.focus(); return; }
                dialog.focus();
            });
        },
        close() {
            this.open = false;
            this.accepting = false;
            this.message = '';
            this.onAccept = null;
        },
        accept() {
            this.accepting = true;
            if (typeof window.adminBeginSubmitGuard === 'function') window.adminBeginSubmitGuard();
            if (typeof this.onAccept === 'function') this.onAccept();
            const self = this;
            setTimeout(() => { if (self.accepting) self.close(); }, 5000);
        },
        handleTab(event, container) {
            if (!(container instanceof HTMLElement)) return;
            const focusable = Array.from(container.querySelectorAll(focusableSelector))
                .filter((el) => el instanceof HTMLElement && !el.hasAttribute('disabled'));
            if (focusable.length === 0) { container.focus(); return; }
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            const active = document.activeElement;
            if (event.shiftKey && active === first) { event.preventDefault(); last.focus(); return; }
            if (!event.shiftKey && active === last) { event.preventDefault(); first.focus(); }
        }
    };
};

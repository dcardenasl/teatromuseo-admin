export function bootConfirmAction() {
    document.addEventListener('click', (event) => {
        const target = event.target;
        const button = target && typeof target.closest === 'function'
            ? target.closest('[data-confirm-message]')
            : null;

        if (!button || window.confirm(button.dataset.confirmMessage || '')) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
    });
}

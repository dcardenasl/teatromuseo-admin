export function togglePermissionGroup(resource, state, form) {
    if (!form) {
        return;
    }

    const selector = `input[type="checkbox"][data-resource="${String(resource).replaceAll('"', '\\"')}"]`;
    form.querySelectorAll(selector).forEach((checkbox) => {
        checkbox.checked = state;
    });

    form.dispatchEvent(new window.Event('change', { bubbles: true }));
}

export function bootPermissionGroupToggle() {
    document.addEventListener('click', (event) => {
        const target = event.target;
        const button = target && typeof target.closest === 'function'
            ? target.closest('[data-permission-group-resource]')
            : null;

        if (!button) {
            return;
        }

        event.preventDefault();
        togglePermissionGroup(
            button.dataset.permissionGroupResource || '',
            button.dataset.permissionGroupState === 'true',
            button.closest('form'),
        );
    });
}

<script <?= csp_script_nonce() ?>>
window.AdminFormFieldErrors = window.AdminFormFieldErrors || {
    normalize(name) {
        return String(name || '')
            .replace(/\[([^\]]*)\]/g, '.$1')
            .replace(/\.+/g, '.')
            .replace(/^\.|\.$/g, '');
    },

    apply(root) {
        const form = root instanceof HTMLFormElement
            ? root
            : root?.querySelector?.('form[data-server-field-errors]');
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        let errors = {};
        try {
            errors = JSON.parse(form.dataset.serverFieldErrors || '{}');
        } catch {
            return;
        }

        const controls = Array.from(form.elements).filter(element => element instanceof HTMLElement && element.name);
        Object.entries(errors).forEach(([field, message]) => {
            const normalizedField = this.normalize(field);
            const matching = controls.filter(control => this.normalize(control.name) === normalizedField);
            if (matching.length === 0) {
                return;
            }

            const errorId = `field-error-${normalizedField.replace(/[^A-Za-z0-9_-]+/g, '-').replace(/^-|-$/g, '')}`;
            let error = form.querySelector(`#${CSS.escape(errorId)}`);
            if (!(error instanceof HTMLElement)) {
                error = document.createElement('p');
                error.id = errorId;
                error.role = 'alert';
                error.className = 'mt-1 text-sm text-red-600';
                error.textContent = String(message || '');
                const target = matching.find(control => control.type !== 'hidden') || matching[0];
                const wrapper = target.closest('.space-y-1, .space-y-2, .space-y-4, .rounded-2xl') || target.parentElement;
                wrapper?.append(error);
            }

            matching.forEach(control => {
                control.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                control.setAttribute('aria-invalid', 'true');
                control.setAttribute('aria-describedby', errorId);
            });
        });
    },
};

document.addEventListener('alpine:initialized', () => {
    window.requestAnimationFrame(() => {
        document.querySelectorAll('form[data-server-field-errors]').forEach(form => {
            window.AdminFormFieldErrors.apply(form);
        });
    });
});
</script>

export function bootEventTranslationSync() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof window.HTMLFormElement)) {
            return;
        }

        const wrapper = form.querySelector('[data-default-translation-index]');
        if (!wrapper) {
            return;
        }

        const index = wrapper.dataset.defaultTranslationIndex;
        ['title', 'slug', 'description'].forEach((field) => {
            const translated = form.querySelector(`[name="translations[${index}][${field}]"]`);
            const canonical = form.querySelector(`[name="${field}"]`);
            if (translated && canonical) {
                canonical.value = translated.value;
            }
        });
    }, true);
}

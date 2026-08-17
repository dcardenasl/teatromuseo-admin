export function openBlockEditPreview(blockKey) {
    const form = document.getElementById('block-edit-form');
    if (!(form instanceof window.HTMLFormElement)) {
        return;
    }

    const langTabs = form.querySelector('[x-ref="langTabs"]')?._x_dataStack?.[0] || null;
    const activeLanguageId = Number(langTabs?.active || 0);
    const activePanel = activeLanguageId > 0
        ? form.querySelector(`[data-language-id="${activeLanguageId}"]`)
        : form.querySelector('[data-language-id]');

    const translatedData = typeof window.formValuesToObject === 'function'
        ? window.formValuesToObject(form)
        : {};
    const blockConfig = translatedData.block_config || {};
    const translationIndex = Number(activePanel?.dataset?.translationIndex || 0);
    const blockData = translatedData.translations?.[String(translationIndex)]?.block_data || {};

    window.dispatchEvent(new window.CustomEvent('block-preview-open', {
        detail: {
            blockKey,
            blockConfig,
            blockData,
            previewMode: 'live',
        },
    }));
}

export function bootBlockEditPreview() {
    document.addEventListener('click', (event) => {
        const target = event.target;
        const button = target && typeof target.closest === 'function'
            ? target.closest('[data-block-preview-key]')
            : null;

        if (!button) {
            return;
        }

        event.preventDefault();
        openBlockEditPreview(button.dataset.blockPreviewKey || '');
    });
}

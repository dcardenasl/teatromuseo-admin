export const blockPreview = () => ({
    isOpen: false,
    loading: false,
    error: '',
    html: '',
    blockKey: '',
    deviceMode: 'desktop',

    init() {
        this.$watch('html', (value) => {
            if (!value) return;
            this.$nextTick(() => {
                const iframe = this.$refs.previewIframe;
                if (!iframe) return;
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const publicSiteUrl = document.querySelector('meta[name="public-site-url"]')?.getAttribute('content') || '';
                
                doc.open();
                doc.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        ${publicSiteUrl ? `<link rel="stylesheet" href="${publicSiteUrl}/assets/css/compiled.css">` : ''}
                        <style>
                            body {
                                background-color: transparent;
                                margin: 0;
                                padding: 1rem;
                                font-family: ui-sans-serif, system-ui, sans-serif;
                            }
                        </style>
                    </head>
                    <body>
                        ${value}
                    </body>
                    </html>
                `);
                doc.close();
            });
        });
    },

    openWithEvent(event) {
        const { blockKey, blockConfig, blockData, previewMode } = event.detail || {};
        this.open(blockKey || '', blockConfig || {}, blockData || {}, previewMode || 'sample');
    },

    open(blockKey, blockConfig, blockData, previewMode = 'sample') {
        this.isOpen  = true;
        this.loading = true;
        this.error   = '';
        this.html    = '';
        this.blockKey = blockKey;
        const effectivePreviewMode = previewMode === 'sample' || previewMode === 'live'
            ? previewMode
            : 'sample';

        const previewUrl = document.querySelector('meta[name="block-preview-url"]')?.getAttribute('content') || '/admin/cms/blocks/preview';
        const csrfInput  = document.querySelector('input[name^="ci4_"][name$="_csrf_token"]') || document.querySelector('input[name="csrf_token"]');
        const csrfName   = csrfInput?.name  || 'csrf_token';
        const csrfToken  = csrfInput?.value || '';

        const body = new URLSearchParams({
            block_key:    blockKey,
            block_config: JSON.stringify(blockConfig),
            block_data:   JSON.stringify(blockData),
            preview_mode: effectivePreviewMode,
            [csrfName]:   csrfToken,
        });

        fetch(previewUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body })
            .then((r) => r.json())
            .then((json) => { this.html = json.html || ''; this.loading = false; })
            .catch((err) => {
                this.error = 'Error al cargar el preview: ' + (err instanceof Error ? err.message : String(err));
                this.loading = false;
            });
    },

    close() { this.isOpen = false; },
});

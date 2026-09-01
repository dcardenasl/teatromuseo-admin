export const blockInstanceConfig = (entryOptionsUrl = '', initialConfig = {}) => ({
    entryOptionsUrl: String(entryOptionsUrl || ''),
    sourceType: String(initialConfig.source_type ?? ''),
    collectionId: String(initialConfig.collection_id ?? ''),
    collectionKey: String(initialConfig.collection_key ?? ''),
    entryId: String(initialConfig.entry_id ?? ''),
    navigationMode: String(initialConfig.navigation_mode ?? 'none'),
    navigationTargetType: String(initialConfig.navigation_target_type ?? 'event_listing'),
    entryOptions: [],
    entryOptionsLoading: false,
    entryOptionsError: '',

    init() {
        if (this.collectionId !== '') {
            void this.refreshEntryOptions(true);
        }
    },

    setInitialConfig(config = {}) {
        this.sourceType = String(config.source_type ?? '');
        this.collectionId = String(config.collection_id ?? '');
        this.collectionKey = String(config.collection_key ?? '');
        this.entryId = String(config.entry_id ?? '');
        this.navigationMode = String(config.navigation_mode ?? 'none');
        this.navigationTargetType = String(config.navigation_target_type ?? 'event_listing');
        if (this.collectionId !== '') {
            void this.refreshEntryOptions(true);
        } else {
            this.entryOptions = [];
            this.entryOptionsError = '';
            this.entryId = '';
        }
    },

    setDefaultsFromFields(configFields = {}) {
        const collectionField = configFields.collection_id || {};
        const entryField = configFields.entry_id || {};

        this.sourceType = String((configFields.source_type || {}).default ?? '');
        this.collectionId = String(collectionField.default ?? '');
        this.collectionKey = String((configFields.collection_key || {}).default ?? '');
        this.entryId = String(entryField.default ?? '');
        this.navigationMode = String(configFields.navigation_mode?.default ?? 'none');
        this.navigationTargetType = String(configFields.navigation_target_type?.default ?? 'event_listing');

        if (this.collectionId !== '') {
            void this.refreshEntryOptions(true);
        } else {
            this.entryOptions = [];
            this.entryOptionsError = '';
            this.entryId = '';
        }
    },

    async refreshEntryOptions(preserveEntry = false) {
        const collectionId = String(this.collectionId || '').trim();
        this.entryOptionsError = '';

        if (collectionId === '' || this.entryOptionsUrl === '') {
            this.entryOptions = [];
            this.entryId = '';
            return;
        }

        this.entryOptionsLoading = true;

        try {
            const url = new URL(this.entryOptionsUrl, window.location.origin);
            url.searchParams.set('collection_id', collectionId);

            const response = await fetch(url.toString(), {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const options = Array.isArray(payload?.options) ? payload.options : [];
            this.entryOptions = options
                .filter((option) => option && typeof option === 'object')
                .map((option) => ({
                    value: String(option.value ?? ''),
                    label: String(option.label ?? option.value ?? ''),
                }))
                .filter((option) => option.value !== '');

            const isValidSelection = this.entryOptions.some((option) => option.value === String(this.entryId));
            if (! preserveEntry || ! isValidSelection) {
                this.entryId = '';
            }
        } catch (error) {
            this.entryOptions = [];
            this.entryOptionsError = error instanceof Error ? error.message : 'Could not load entries.';
            if (! preserveEntry) {
                this.entryId = '';
            }
        } finally {
            this.entryOptionsLoading = false;
        }
    },

    async onCollectionChange(value) {
        this.collectionId = String(value ?? '');
        this.entryId = '';
        await this.refreshEntryOptions(false);
    },

    onCollectionKeyChange(value) {
        this.collectionKey = String(value ?? '');
    },

    onSourceTypeChange(value) {
        this.sourceType = String(value ?? '');
    },
});

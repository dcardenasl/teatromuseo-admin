const findRichTextEditorComponent = (input) => {
    const container = input instanceof HTMLElement ? input.closest('[x-data*="richTextEditor"]') : null;
    const component = container?._x_dataStack?.[0];
    return component && typeof component.applyContent === 'function' ? component : null;
};

const applyTranslatedText = (targetInput, translatedValue) => {
    if (!(targetInput instanceof HTMLInputElement || targetInput instanceof window.HTMLTextAreaElement)) {
        return;
    }

    targetInput.value = translatedValue;
    targetInput.dispatchEvent(new window.Event('input', { bubbles: true }));

    const richTextComponent = findRichTextEditorComponent(targetInput);
    if (richTextComponent) {
        richTextComponent.applyContent(translatedValue);
    }
};

export function blockInstanceBuilder(blockTypes, languages, entryOptionsUrl = '', translateUrl = '', defaultLangCode = 'ES', initialBlockId = 0) {
    const configFactory = typeof window.blockInstanceConfigFactory === 'function'
        ? window.blockInstanceConfigFactory(entryOptionsUrl, {})
        : {};

    return {
        ...configFactory,
        blockTypes,
        languages,
        entryOptionsUrl,
        translateUrl,
        defaultLangCode,
        selectedBlockType: null,
        activeLangId: null,
        contentFields: {},
        configFields: {},
        blockTypeSearch: '',

        // Filters the Paso 1 catalog by name/block_key/category — 30+ ungrouped
        // cards was hard to scan for a specific block type.
        filteredBlockTypes() {
            const q = this.blockTypeSearch.trim().toLowerCase();
            if (q === '') return this.blockTypes;
            return this.blockTypes.filter(bt => {
                const haystack = [bt.name, bt.block_key, bt.category, bt.description]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();
                return haystack.includes(q);
            });
        },

        translating: false,
        translatingAll: false,
        translateError: '',
        translateAllProgress: '',

        // Repeater state: keyed by `${langId}_${fieldKey}`
        repeaterItems: {},

        // Picked file metadata keyed by `${langId}_${fieldKey}` (top-level file fields)
        pickedFilesMap: {},

        dateFieldLabel(value) {
            const labels = {
                auto: 'Automática', published_at: 'Fecha de publicación', created_at: 'Fecha de creación',
                'listing.publication_date': 'Fecha editorial', 'listing.start_date': 'Fecha de inicio', 'listing.end_date': 'Fecha de término',
                'listing.opening_date': 'Fecha de inauguración', 'listing.closing_date': 'Fecha de cierre', 'listing.premiere_date': 'Fecha de estreno',
                'listing.performance_date': 'Fecha de función', 'listing.recorded_at': 'Fecha de registro',
            };
            return labels[String(value)] || value;
        },

        init() {
            const def = this.languages.find(l => l.is_default == 1);
            this.activeLangId = def ? def.id : (this.languages[0]?.id || null);
            if (typeof configFactory.init === 'function') {
                configFactory.init.call(this);
            }
            const initialBlock = this.blockTypes.find(bt => Number(bt.id) === Number(initialBlockId));
            if (initialBlock) {
                this.selectBlockType(initialBlock);
            }
            window.requestAnimationFrame(() => window.AdminFormFieldErrors?.apply(this.$root));
        },

        selectBlockType(bt) {
            this.selectedBlockType = bt;
            const schema = bt.schema_definition || {};
            this.contentFields = Object.fromEntries(
                Object.entries(schema.fields || {})
            );
            this.configFields  = schema.config_fields || {};
            this.repeaterItems = {};
            this.pickedFilesMap = {};
            if (typeof this.setDefaultsFromFields === 'function') {
                this.setDefaultsFromFields(this.configFields || {});
            }
            if (typeof window.lucide !== 'undefined') { setTimeout(() => window.lucide.createIcons(), 50); }
        },

        // ── Repeater helpers ─────────────────────────────────────────────────
        repeaterList(langId, fieldKey) {
            const k = `${langId}_${fieldKey}`;
            if (!this.repeaterItems[k]) this.repeaterItems[k] = [];
            return this.repeaterItems[k];
        },

        isImageAccept(accept) {
            const normalized = String(accept || '').trim().toLowerCase();
            return normalized === 'image'
                || normalized === 'image/*'
                || normalized.startsWith('image/');
        },

        normalizeMediaReferenceValue(value = {}) {
            const raw = (value && typeof value === 'object' && !Array.isArray(value)) ? value : {};
            const fileId = String(raw.file_id ?? raw.fileId ?? '');
            const url = String(raw.url ?? raw.external_url ?? '');
            let sourceKind = String(raw.source_kind ?? raw.sourceKind ?? '');

            if (!sourceKind) {
                sourceKind = fileId !== '' || /\/files\/\d+\/(?:view|download)(?:\?.*)?$/i.test(url)
                    ? 'hub_file'
                    : (url !== '' ? 'external_url' : 'hub_file');
            }

            return {
                source_kind: sourceKind,
                file_id: sourceKind === 'external_url' ? '' : fileId,
                url,
            };
        },

        normalizeRepeaterItem(itemFields, item = {}) {
            const normalized = {};
            Object.keys(itemFields || {}).forEach(subKey => {
                const subField = itemFields[subKey] || {};
                if (subField.type === 'media_reference' || (subField.type === 'file' && this.isImageAccept(subField.accept))) {
                    normalized[subKey] = this.normalizeMediaReferenceValue(
                        item[subKey] || {
                            source_kind: item[subKey + '_source_kind'] || '',
                            file_id: item[subKey + '_file_id'] || '',
                            url: item[subKey + '_url'] || '',
                        }
                    );
                } else if (subField.type === 'file') {
                    normalized[subKey + '_file_id'] = String(item[subKey + '_file_id'] || '');
                    normalized[subKey + '_preview_url'] = '';
                    normalized[subKey + '_url'] = String(item[subKey + '_url'] || '');
                } else {
                    normalized[subKey] = item[subKey] ?? '';
                }
            });

            return normalized;
        },

        addItem(langId, fieldKey, itemFields) {
            const k = `${langId}_${fieldKey}`;
            if (!this.repeaterItems[k]) this.repeaterItems[k] = [];
            const item = this.normalizeRepeaterItem(itemFields, {});
            this.repeaterItems[k].push(item);
        },

        removeItem(langId, fieldKey, idx) {
            const k = `${langId}_${fieldKey}`;
            if (this.repeaterItems[k]) this.repeaterItems[k].splice(idx, 1);
        },

        // ── File picker helpers ───────────────────────────────────────────────
        getPickedFileId(langId, fieldKey) {
            return (this.pickedFilesMap[`${langId}_${fieldKey}`] || {}).id || '';
        },

        getPickedFileUrl(langId, fieldKey) {
            return (this.pickedFilesMap[`${langId}_${fieldKey}`] || {}).url || '';
        },

        openFilePicker(callback, accept) {
            const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
            const filterType = filterTypeMap[accept] ?? 'image';
            const mimeAccept = (!accept || accept === 'any') ? ''
                : accept.includes('/') ? accept
                : accept + '/*';
            Alpine.store('filePicker').show({
                filterType,
                accept: mimeAccept,
                multi: false,
                onSelect: (file) => callback(file),
            });
        },

        // pickFile is called by the openFilePicker callback.
        // For top-level file fields: langId, fieldKey, itemIdx=null, subKey=null
        // For repeater sub-fields: all four are set
        pickFile(langId, fieldKey, itemIdx, subKey, file) {
            if (itemIdx === null) {
                this.pickedFilesMap[`${langId}_${fieldKey}`] = { id: file.id, url: file.url, preview_url: window.bestFilePreviewUrl ? window.bestFilePreviewUrl(file) : file.url };
            } else {
                const k = `${langId}_${fieldKey}`;
                if (this.repeaterItems[k] && this.repeaterItems[k][itemIdx]) {
                    this.repeaterItems[k][itemIdx][subKey + '_file_id']     = file.id;
                    this.repeaterItems[k][itemIdx][subKey + '_url']         = file.url;
                    this.repeaterItems[k][itemIdx][subKey + '_preview_url'] = window.bestFilePreviewUrl ? window.bestFilePreviewUrl(file) : file.url;
                }
            }
        },

        clearPickedFile(langId, fieldKey) {
            this.pickedFilesMap[`${langId}_${fieldKey}`] = { id: '', url: '', preview_url: '' };
        },

        copyFileToAllLanguages(sourceLangId, fieldKey) {
            const sourceFile = this.pickedFilesMap[`${sourceLangId}_${fieldKey}`];
            if (!sourceFile || !sourceFile.id) return;

            const updatedMap = { ...this.pickedFilesMap };
            this.languages.forEach(lang => {
                if (Number(lang.id) !== Number(sourceLangId)) {
                    updatedMap[`${lang.id}_${fieldKey}`] = {
                        id: sourceFile.id,
                        url: sourceFile.url,
                        preview_url: sourceFile.preview_url
                    };
                }
            });
            this.pickedFilesMap = updatedMap;
        },

        getTranslateTargets() {
            if (!this.selectedBlockType) return [];

            const defLang = this.languages.find(l => l.is_default == 1);
            if (!defLang) return [];

            const defLangIndex = this.languages.findIndex(l => l.is_default == 1);

            const translatableFieldKeys = [];
            Object.entries(this.contentFields).forEach(([fieldKey, field]) => {
                const fieldType = field.type || 'string';
                if (!['file', 'media_reference', 'repeater', 'boolean', 'integer', 'number', 'select', 'entry_reference', 'entry_reference_list'].includes(fieldType)) {
                    translatableFieldKeys.push(fieldKey);
                }
            });

            if (translatableFieldKeys.length === 0) return [];

            const targets = [];
            this.languages.forEach((lang, idx) => {
                if (idx === defLangIndex) return;

                const fieldPairs = [];
                translatableFieldKeys.forEach(fieldKey => {
                    fieldPairs.push({
                        from: `[name="translations[${defLangIndex}][block_data][${fieldKey}]"]`,
                        to: `[name="translations[${idx}][block_data][${fieldKey}]"]`
                    });
                });

                targets.push({
                    langCode: lang.code.toUpperCase(),
                    fieldPairs: fieldPairs
                });
            });

            return targets;
        },

        async _translatePairs(targetLangCode, fieldPairs) {
            for (const pair of fieldPairs) {
                const sourceEl = document.querySelector(pair.from);
                const targetEl = document.querySelector(pair.to);
                if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof window.HTMLTextAreaElement)) continue;
                if (!(targetEl instanceof HTMLInputElement || targetEl instanceof window.HTMLTextAreaElement)) continue;
                const sourceText = sourceEl.value.trim();
                if (sourceText === '') continue;

                const url = new URL(this.translateUrl, window.location.origin);
                url.searchParams.set('text', sourceText);
                url.searchParams.set('source_lang', this.defaultLangCode.toUpperCase());
                url.searchParams.set('target_lang', targetLangCode.toUpperCase());

                const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                const json = await res.json();
                if (json && typeof json.translated === 'string') {
                    applyTranslatedText(targetEl, json.translated);
                } else if (json && json.error) {
                    throw new Error(json.error);
                }
            }
        },

        async autoTranslateAll() {
            const targets = this.getTranslateTargets();
            if (this.translateUrl === '' || this.translating || this.translatingAll || targets.length === 0) return;

            this.translatingAll = true;
            this.translateError = '';
            try {
                for (let i = 0; i < targets.length; i++) {
                    const { langCode, fieldPairs } = targets[i];
                    this.translateAllProgress = langCode + ' (' + (i + 1) + '/' + targets.length + ')';
                    await this._translatePairs(langCode, fieldPairs);
                }
                this.translateAllProgress = '';
            } catch (e) {
                this.translateError = e instanceof Error ? e.message : String(e);
                this.translateAllProgress = '';
            } finally {
                this.translatingAll = false;
            }
        },

        openPreview() {
            if (!this.selectedBlockType) return;
            const form = this.$root instanceof HTMLElement ? this.$root.querySelector('form') : null;
            const payload = typeof window.formValuesToObject === 'function'
                ? window.formValuesToObject(form)
                : {};
            const config = payload.block_config || {};
            window.dispatchEvent(new CustomEvent('block-preview-open', {
                detail: { blockKey: this.selectedBlockType.block_key, blockConfig: config, blockData: {}, previewMode: 'live' },
            }));
        },
    };
}

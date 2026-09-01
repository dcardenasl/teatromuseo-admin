export function collectionBlockTemplateBuilder(blockTypes, initialTemplate, collectionPresets, initialWizardConfig, wizardFieldCatalog, uiLabels = {}) {
    return {
        uiLabels: uiLabels && typeof uiLabels === 'object' ? uiLabels : {},
        blockTypes: Array.isArray(blockTypes) ? blockTypes : [],
        collectionPresets: Array.isArray(collectionPresets) ? collectionPresets : [],
        wizardConfig: initialWizardConfig || null,
        wizardConfigJson: '',
        wizardFieldCatalog: wizardFieldCatalog && typeof wizardFieldCatalog === 'object' ? wizardFieldCatalog : {},
        stepRows: [],
        activePanel: 'catalog',
        rows: [],
        json: '',
        valid: false,
        error: '',
        initialTemplate: initialTemplate && typeof initialTemplate === 'object' ? initialTemplate : { version: '1.0', blocks: [] },
        toBoolean(value, defaultValue = false) {
            if (value === null || value === undefined || value === '') {
                return defaultValue;
            }

            if (typeof value === 'boolean') {
                return value;
            }

            if (typeof value === 'number') {
                return value !== 0;
            }

            if (typeof value === 'string') {
                const normalized = value.trim().toLowerCase();
                if (['1', 'true', 'yes', 'on'].includes(normalized)) {
                    return true;
                }
                if (['0', 'false', 'no', 'off'].includes(normalized)) {
                    return false;
                }
            }

            return Boolean(value);
        },

        toInteger(value, defaultValue) {
            const parsed = Number.parseInt(value, 10);
            return Number.isFinite(parsed) ? parsed : defaultValue;
        },

        init() {
            this.rows = this.normalizeRowsFromTemplate(this.initialTemplate);
            this.stepRows = this.normalizeStepRowsFromWizardConfig(this.wizardConfig);
            this.activePanel = this.rows.length > 0 ? 'structure' : 'catalog';
            this.sync();
        },

        // ── Wizard steps (wizard_config.steps) ─────────────────────────────
        // Fixed catalog of native entry-level fields (see WizardStepFieldCatalog
        // on the Domain) — deliberately not open to arbitrary keys, see the PHP
        // docblock above where wizardFieldCatalog is built.
        wizardFieldLabel(key) {
            return this.wizardFieldCatalog[key]?.label || String(key || '');
        },

        usedWizardFieldKeys() {
            const used = [];
            this.stepRows.forEach((step) => {
                (step.fields || []).forEach((field) => used.push(field.key));
            });
            return used;
        },

        availableWizardFields() {
            const used = this.usedWizardFieldKeys();
            return Object.keys(this.wizardFieldCatalog).filter((key) => !used.includes(key));
        },

        wizardTitleMissing() {
            return this.stepRows.length > 0 && !this.usedWizardFieldKeys().includes('title');
        },

        normalizeStepRowsFromWizardConfig(config) {
            const steps = Array.isArray(config?.steps) ? config.steps : [];
            const seen = new Set();

            return steps.map((step) => ({
                step_title: String(step?.step_title || ''),
                step_hint: String(step?.step_hint || ''),
                fields: (Array.isArray(step?.fields) ? step.fields : [])
                    .filter((field) => {
                        const key = field?.key;
                        // Drop keys outside the catalog (defense in depth — the
                        // Domain rejects them too) and any duplicate reuse of a
                        // key across steps, since the catalog model is "each
                        // native field lives in at most one step."
                        if (!key || !this.wizardFieldCatalog[key] || seen.has(key)) {
                            return false;
                        }
                        seen.add(key);
                        return true;
                    })
                    .map((field) => ({
                        key: field.key,
                        label: String(field.label || this.wizardFieldLabel(field.key)),
                        required: Boolean(field.required),
                    })),
            }));
        },

        addStep() {
            this.stepRows.push({ step_title: '', step_hint: '', fields: [] });
            this.sync();
        },

        removeStep(index) {
            this.stepRows.splice(index, 1);
            this.sync();
        },

        moveStep(index, delta) {
            const targetIndex = index + delta;
            if (targetIndex < 0 || targetIndex >= this.stepRows.length) {
                return;
            }

            const rows = [...this.stepRows];
            const [row] = rows.splice(index, 1);
            rows.splice(targetIndex, 0, row);
            this.stepRows = rows;
            this.sync();
        },

        addFieldToStep(stepIndex, key) {
            if (!key || !this.stepRows[stepIndex] || !this.wizardFieldCatalog[key]) {
                return;
            }
            if (this.usedWizardFieldKeys().includes(key)) {
                return;
            }

            this.stepRows[stepIndex].fields.push({
                key,
                label: this.wizardFieldLabel(key),
                required: key === 'title', // matches WizardStepFieldCatalog::ANCHOR_KEY on the Domain
            });
            this.sync();
        },

        removeFieldFromStep(stepIndex, fieldIndex) {
            const step = this.stepRows[stepIndex];
            if (!step || !step.fields[fieldIndex]) {
                return;
            }

            step.fields.splice(fieldIndex, 1);
            this.sync();
        },

        buildWizardConfigSteps() {
            return this.stepRows
                .filter((step) => step.fields.length > 0)
                .map((step) => ({
                    step_title: String(step.step_title || '').trim(),
                    step_hint: String(step.step_hint || '').trim(),
                    fields: step.fields.map((field) => ({
                        key: field.key,
                        label: String(field.label || '').trim() || this.wizardFieldLabel(field.key),
                        type: this.wizardFieldCatalog[field.key]?.type || 'text',
                        required: Boolean(field.required),
                    })),
                }));
        },

        blockTypeLabel(blockKey) {
            const item = this.blockTypes.find((bt) => bt.block_key === blockKey);
            return item ? item.name : (blockKey || this.uiLabels.blockFallback || 'Block');
        },

        blockTypeByKey(blockKey) {
            return this.blockTypes.find((bt) => bt.block_key === blockKey) || null;
        },

        createRowFromBlockType(blockType) {
            return {
                block_key: blockType.block_key || '',
                label: blockType.name || blockType.block_key || '',
                help_text: blockType.description || '',
                sort_order: this.rows.length + 1,
                required: true,
                locked: false,
                advancedOpen: false,
                defaults: [],
            };
        },

        normalizeRowsFromTemplate(template) {
            if (!template || typeof template !== 'object' || !Array.isArray(template.blocks)) {
                return [];
            }

            return template.blocks
                .filter((block) => block && typeof block === 'object' && typeof block.block_key === 'string' && block.block_key !== '')
                .map((block, index) => {
                    const defaults = [];
                    const rawDefaults = block.block_config_defaults && typeof block.block_config_defaults === 'object'
                        ? block.block_config_defaults
                        : {};

                    Object.entries(rawDefaults).forEach(([key, value]) => {
                        defaults.push({
                            key,
                            type: typeof value === 'boolean' ? 'boolean' : (typeof value === 'number' ? 'number' : 'string'),
                            value: typeof value === 'boolean' ? (value ? '1' : '0') : String(value ?? ''),
                        });
                    });

                    const blockType = this.blockTypeByKey(block.block_key);
                    return {
                        block_key: block.block_key,
                        label: typeof block.label === 'string' && block.label !== '' ? block.label : (blockType?.name || block.block_key),
                        help_text: typeof block.help_text === 'string' ? block.help_text : (blockType?.description || ''),
                        sort_order: Number.isInteger(block.sort_order) ? block.sort_order : this.toInteger(block.sort_order, index + 1),
                        required: this.toBoolean(block.required, true),
                        locked: this.toBoolean(block.locked, false),
                        advancedOpen: this.toBoolean(block.advancedOpen, false),
                        defaults,
                    };
                })
                .sort((a, b) => a.sort_order - b.sort_order);
        },

        normalizeSortOrders() {
            this.rows = this.rows.map((row, index) => ({
                ...row,
                sort_order: index + 1,
                defaults: Array.isArray(row.defaults) ? row.defaults : [],
                advancedOpen: this.toBoolean(row.advancedOpen, false),
            }));
        },

        loadPreset(presetTypeKey) {
            if (!presetTypeKey) return;
            const preset = this.collectionPresets.find((p) => p.type_key === presetTypeKey);
            if (!preset) return;

            const confirmMsg = this.uiLabels.presetConfirm || 'Are you sure you want to load this preset? It will overwrite the current block template.';
            if (this.rows.length > 0 && !window.confirm(confirmMsg)) {
                return;
            }

            this.rows = this.normalizeRowsFromTemplate(preset.block_template);
            this.wizardConfig = preset.wizard_config || null;
            this.stepRows = this.normalizeStepRowsFromWizardConfig(this.wizardConfig);
            this.activePanel = 'structure';
            this.sync();
        },

        setActivePanel(panel) {
            this.activePanel = ['structure', 'wizard-steps'].includes(panel) ? panel : 'catalog';
            if (this.activePanel === 'structure' && this.rows.length === 0) {
                this.activePanel = 'catalog';
            }
        },

        addBlock(blockKey) {
            const blockType = this.blockTypeByKey(blockKey);
            if (!blockType) {
                return;
            }

            this.rows.push(this.createRowFromBlockType(blockType));
            this.activePanel = 'structure';
            this.sync();
            this.scrollToTemplate();
        },

        removeBlock(index) {
            this.rows.splice(index, 1);
            if (this.rows.length === 0) {
                this.activePanel = 'catalog';
            }
            this.sync();
        },

        moveBlock(index, delta) {
            const targetIndex = index + delta;
            if (targetIndex < 0 || targetIndex >= this.rows.length) {
                return;
            }

            const rows = [...this.rows];
            const [row] = rows.splice(index, 1);
            rows.splice(targetIndex, 0, row);
            this.rows = rows;
            this.sync();
        },

        onBlockKeyChanged(index) {
            const blockType = this.blockTypeByKey(this.rows[index]?.block_key || '');
            if (!blockType) {
                this.sync();
                return;
            }

            const row = this.rows[index];
            if (!row.label) {
                row.label = blockType.name || row.block_key;
            }
            if (!row.help_text) {
                row.help_text = blockType.description || '';
            }
            this.sync();
        },

        addDefault(blockIndex) {
            if (!this.rows[blockIndex]) {
                return;
            }

            this.rows[blockIndex].defaults.push({
                key: '',
                type: 'string',
                value: '',
            });
            this.sync();
        },

        removeDefault(blockIndex, defaultIndex) {
            if (!this.rows[blockIndex] || !Array.isArray(this.rows[blockIndex].defaults)) {
                return;
            }

            this.rows[blockIndex].defaults.splice(defaultIndex, 1);
            this.sync();
        },

        castDefaultValue(defaultRow) {
            if (!defaultRow) {
                return null;
            }

            if (defaultRow.type === 'boolean') {
                return defaultRow.value === '1' || defaultRow.value === 1 || defaultRow.value === true || defaultRow.value === 'true';
            }

            if (defaultRow.type === 'number') {
                const parsed = Number(defaultRow.value);
                return Number.isFinite(parsed) ? parsed : null;
            }

            return String(defaultRow.value ?? '');
        },

        serializeDefaults(defaultRows) {
            const defaults = {};
            (Array.isArray(defaultRows) ? defaultRows : []).forEach((defaultRow) => {
                const key = String(defaultRow?.key ?? '').trim();
                if (!key) {
                    return;
                }

                defaults[key] = this.castDefaultValue(defaultRow);
            });
            return defaults;
        },

        buildTemplate() {
            const blocks = this.rows
                .filter((row) => String(row.block_key || '').trim() !== '')
                .map((row, index) => {
                    const label = String(row.label || '').trim();
                    const helpText = String(row.help_text || '').trim();
                    return {
                        block_key: String(row.block_key || '').trim(),
                        label: label !== '' ? label : undefined,
                        sort_order: index + 1,
                        required: Boolean(row.required),
                        locked: Boolean(row.locked),
                        help_text: helpText !== '' ? helpText : undefined,
                        block_config_defaults: this.serializeDefaults(row.defaults),
                    };
                });

            return {
                version: '1.0',
                blocks,
            };
        },

        sync() {
            this.normalizeSortOrders();
            const template = this.buildTemplate();
            this.json = JSON.stringify(template, null, 2);
            this.valid = template.blocks.length > 0;
            this.error = '';

            // Rebuild wizard_config.steps from stepRows on every change — mirrors
            // buildTemplate() for blocks. Preserves any other top-level keys
            // (e.g. `type`) already present on wizardConfig.
            const wizardSteps = this.buildWizardConfigSteps();
            if (wizardSteps.length > 0) {
                this.wizardConfig = { ...(this.wizardConfig || {}), steps: wizardSteps };
            } else if (this.wizardConfig) {
                const rest = { ...this.wizardConfig };
                delete rest.steps;
                this.wizardConfig = Object.keys(rest).length > 0 ? rest : null;
            }

            this.wizardConfigJson = this.wizardConfig ? JSON.stringify(this.wizardConfig, null, 2) : '';

            if (this.$refs?.blockTemplateInput) {
                this.$refs.blockTemplateInput.value = this.json;
            }
            if (this.$refs?.wizardConfigInput) {
                this.$refs.wizardConfigInput.value = this.wizardConfigJson;
            }
        },

        scrollToTemplate() {
            this.$nextTick(() => {
                if (this.$refs.templateList && typeof this.$refs.templateList.scrollIntoView === 'function') {
                    this.$refs.templateList.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
    };
}

const DEFAULT_SOURCE_KEY = 'manual';

const DEFAULT_SOURCES = [
    { key: 'manual', label: 'Manual', description: 'Bloque libre con contenido y configuración definidos a mano.' },
    { key: 'page', label: 'Página', description: 'Bloque vinculado a una página concreta.' },
    { key: 'collection', label: 'Colección', description: 'Bloque que consume una colección y su configuración.' },
    { key: 'entry', label: 'Entrada', description: 'Bloque vinculado a una entrada individual.' },
    { key: 'container', label: 'Contenedor', description: 'Bloque contenedor para agrupar bloques hijos.' },
];

export const blockTypeDesigner = (templates = [], sourceKinds = DEFAULT_SOURCES, initialSchema = null) => ({
    templates,
    sourceKinds,
    selectedSource: null,
    selectedTemplate: null,
    customMode: false,
    customBlockKey: '',
    schemaFields: [],
    configFields: [],
    schemaJson: '{}',
    isContainer: false,
    allowedChildren: [],

    get effectiveBlockKey() {
        return this.customMode ? this.customBlockKey : (this.selectedTemplate?.key || '');
    },

    get visibleTemplates() {
        const sourceKey = this.selectedSource?.key || DEFAULT_SOURCE_KEY;
        return this.templates.filter((template) => this.templateSourceKey(template) === sourceKey);
    },

    init() {
        if (initialSchema) {
            this.loadFromSchema(initialSchema);
            return;
        }

        this.selectedSource = this._sourceForKey(DEFAULT_SOURCE_KEY);
    },

    _sourceForKey(key) {
        const lookupKey = String(key || '').trim() || DEFAULT_SOURCE_KEY;
        return this.sourceKinds.find((option) => option.key === lookupKey)
            || DEFAULT_SOURCES.find((option) => option.key === lookupKey)
            || { key: lookupKey, label: lookupKey, description: '' };
    },

    templateSourceKey(template) {
        return template?.content_source?.type || template?.content_source_type || DEFAULT_SOURCE_KEY;
    },

    selectSource(source) {
        const sourceKey = typeof source === 'string' ? source : (source?.key || DEFAULT_SOURCE_KEY);
        this.selectedSource = this._sourceForKey(sourceKey);
        if (this.selectedTemplate && this.templateSourceKey(this.selectedTemplate) !== (this.selectedSource?.key || DEFAULT_SOURCE_KEY)) {
            this.selectedTemplate = null;
            this.customMode = false;
            this._applySourceDefaults();
        }
        if (!this.selectedTemplate && this.customMode) {
            this._applySourceDefaults();
        }
        this.rebuildJson();
    },

    selectTemplate(template) {
        this.selectedTemplate = template;
        this.customMode = false;
        this.selectedSource = this._sourceForKey(this.templateSourceKey(template));
        const schema = template.default_schema || {};
        this.schemaFields = this._schemaToRows(schema.fields || {});
        this.configFields = this._schemaToRows(schema.config_fields || {});
        this.isContainer = (template.content_source?.type || template.content_source_type || '') === 'container' || schema.allowed_children !== undefined;
        this.allowedChildren = schema.allowed_children || [];
        this.rebuildJson();
    },

    enableCustomMode() {
        this.selectedTemplate = null;
        this.customMode = true;
        if (!this.selectedSource) {
            this.selectedSource = this._sourceForKey(DEFAULT_SOURCE_KEY);
        }
        this._applySourceDefaults();
        this.rebuildJson();
    },

    loadFromSchema(schema) {
        this.selectedSource = this._sourceForKey(schema.content_source?.type || schema.content_source_type || (schema.allowed_children ? 'container' : DEFAULT_SOURCE_KEY));
        this.schemaFields = this._schemaToRows(schema.fields || {});
        this.configFields = this._schemaToRows(schema.config_fields || {});
        this.isContainer = schema.content_source?.type === 'container' || !!schema.allowed_children;
        this.allowedChildren = schema.allowed_children || [];
        this.rebuildJson();
    },

    _applySourceDefaults() {
        const sourceKey = this.selectedSource?.key || DEFAULT_SOURCE_KEY;
        const preset = this._schemaPreset(sourceKey);
        this.schemaFields = this._schemaToRows(preset.fields || {});
        this.configFields = this._schemaToRows(preset.config_fields || {});
        this.isContainer = sourceKey === 'container';
        this.allowedChildren = preset.allowed_children || [];
    },

    _schemaPreset(sourceKey) {
        if (sourceKey === 'page') {
            return {
                fields: {
                    heading: { type: 'string', label: 'Título', required: true },
                    body: { type: 'text', label: 'Contenido', required: false },
                },
                config_fields: {
                    page_id: { type: 'select', label: 'Página vinculada', required: true },
                    css_class: { type: 'string', label: 'Clase CSS', required: false, default: '' },
                },
            };
        }

        if (sourceKey === 'collection') {
            return {
                fields: {
                    heading: { type: 'string', label: 'Título', required: true },
                    intro: { type: 'text', label: 'Introducción', required: false },
                },
                config_fields: {
                    collection_id: { type: 'select', label: 'Colección vinculada', required: true },
                    items_limit: { type: 'number', label: 'Máx. elementos', required: false, default: 3 },
                    order_by: { type: 'select', label: 'Ordenar por', required: false, default: 'published_at' },
                    order_direction: { type: 'select', label: 'Dirección', required: false, default: 'desc' },
                    css_class: { type: 'string', label: 'Clase CSS', required: false, default: '' },
                },
            };
        }

        if (sourceKey === 'entry') {
            return {
                fields: {
                    heading: { type: 'string', label: 'Título', required: true },
                    intro: { type: 'text', label: 'Introducción', required: false },
                },
                config_fields: {
                    collection_id: { type: 'select', label: 'Colección', required: true },
                    entry_id: { type: 'select', label: 'Entrada', required: true },
                    css_class: { type: 'string', label: 'Clase CSS', required: false, default: '' },
                },
            };
        }

        if (sourceKey === 'container') {
            return {
                fields: {},
                config_fields: {
                    css_class: { type: 'string', label: 'Clase CSS', required: false, default: 'container mx-auto px-4' },
                    layout: { type: 'select', label: 'Distribución', required: false, default: 'block' },
                },
                allowed_children: [],
            };
        }

        return { fields: {}, config_fields: {} };
    },

    _schemaToRows(fieldsObj) {
        return Object.entries(fieldsObj).map(([key, def]) => ({
            key,
            type:     def.type    || 'string',
            label:    def.label   || key,
            required: def.required === true || def.required === 1,
            options:  Array.isArray(def.options) ? def.options.join(', ') : '',
            default:  def.default || '',
            accept:   def.accept || 'image',
            item_fields: def.item_fields ? this._schemaToRows(def.item_fields) : [],
        }));
    },

    addField(section, parentRow = null) {
        const row = { key: '', type: 'string', label: '', required: false, options: '', default: '', accept: 'image', item_fields: [] };
        if (parentRow) { if (!parentRow.item_fields) parentRow.item_fields = []; parentRow.item_fields.push(row); }
        else if (section === 'config') { this.configFields.push(row); }
        else { this.schemaFields.push(row); }
        this.rebuildJson();
    },

    removeField(section, index, parentRow = null) {
        if (parentRow) { parentRow.item_fields.splice(index, 1); }
        else if (section === 'config') { this.configFields.splice(index, 1); }
        else { this.schemaFields.splice(index, 1); }
        this.rebuildJson();
    },

    rebuildJson() {
        const buildObj = (rows) => {
            const obj = {};
            for (const row of rows) {
                if (!row.key) continue;
                const def = { type: row.type, label: row.label, required: row.required };
                if (row.type === 'select' && row.options) def.options = row.options.split(',').map((s) => s.trim()).filter(Boolean);
                if (row.type === 'media_reference') def.accept = row.accept || 'image';
                if (row.type === 'repeater') def.item_fields = buildObj(row.item_fields || []);
                if (row.default !== '') def.default = row.default;
                obj[row.key] = def;
            }
            return obj;
        };
        const schema = {
            fields: buildObj(this.schemaFields),
            config_fields: buildObj(this.configFields),
            content_source: {
                type: this.selectedSource?.key || DEFAULT_SOURCE_KEY,
                label: this.selectedSource?.label || '',
                description: this.selectedSource?.description || '',
            },
        };
        if (this.isContainer) schema.allowed_children = this.allowedChildren || [];
        this.schemaJson = JSON.stringify(schema, null, 2);
    },

    openPreview() {
        const sampleData   = this.selectedTemplate?.preview_sample || {};
        const sampleConfig = this.selectedTemplate?.config_sample  || {};
        const key = this.selectedTemplate?.key || document.getElementById('block_key')?.value || '';
        window.dispatchEvent(new CustomEvent('block-preview-open', {
            detail: { blockKey: key, blockConfig: sampleConfig, blockData: sampleData, previewMode: 'sample' },
        }));
    },

    isSelected(template) { return this.selectedTemplate?.key === template.key; },
    isSourceSelected(source) { return (this.selectedSource?.key || DEFAULT_SOURCE_KEY) === source.key; },
});

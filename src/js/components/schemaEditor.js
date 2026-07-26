const DEFAULT_SOURCE_KEY = 'manual';

const DEFAULT_SOURCES = [
    { key: 'manual', label: 'Manual', description: 'Bloque libre con contenido y configuración definidos a mano.' },
    { key: 'page', label: 'Página', description: 'Bloque vinculado a una página concreta.' },
    { key: 'collection', label: 'Colección', description: 'Bloque que consume una colección y su configuración.' },
    { key: 'entry', label: 'Entrada', description: 'Bloque vinculado a una entrada individual.' },
    { key: 'container', label: 'Contenedor', description: 'Bloque contenedor para agrupar bloques hijos.' },
];

export const schemaEditor = (initialSchema = {}, initialIsContainer = false, sourceKinds = DEFAULT_SOURCES) => ({
    sourceKinds,
    selectedSource: null,
    schemaFields: [],
    configFields: [],
    schemaJson: '{}',
    isContainer: initialIsContainer,
    allowedChildren: [],

    init() {
        const sourceKey = initialSchema.content_source?.type || initialSchema.content_source_type || (initialSchema.allowed_children ? 'container' : DEFAULT_SOURCE_KEY);
        this.selectedSource = this._sourceForKey(sourceKey);
        this.schemaFields = this._schemaToRows(initialSchema.fields || {});
        this.configFields = this._schemaToRows(initialSchema.config_fields || {});
        this.allowedChildren = initialSchema.allowed_children || [];
        this.rebuildJson();
    },

    _sourceForKey(key) {
        const lookupKey = String(key || '').trim() || DEFAULT_SOURCE_KEY;
        return this.sourceKinds.find((option) => option.key === lookupKey)
            || DEFAULT_SOURCES.find((option) => option.key === lookupKey)
            || { key: lookupKey, label: lookupKey, description: '' };
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

    setSource(source) {
        this.selectedSource = this._sourceForKey(typeof source === 'string' ? source : (source?.key || DEFAULT_SOURCE_KEY));
        this.isContainer = this.selectedSource?.key === 'container';
        if (! this.isContainer) {
            this.allowedChildren = [];
        }
        this.rebuildJson();
    },

    selectSource(source) {
        this.setSource(source);
    },

    isSourceSelected(source) {
        return (this.selectedSource?.key || DEFAULT_SOURCE_KEY) === source.key;
    },
});

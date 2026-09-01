export function listingProjectionEditor(catalog, initial = {}, initialSource = '', initialCollection = '') {
    const raw = typeof initial === 'object' && initial !== null ? initial : {};
    const normalizeItems = (items) => Array.isArray(items) ? items.map((item, index) => ({
        id: item.id || `${Date.now()}-${index}-${Math.random().toString(36).slice(2)}`,
        source: String(item.source || ''),
        label: String(item.label || ''),
        operator: String(item.operator || 'equals'),
    })) : [];
    const projectionVersion = Number(raw.version);
    const projection = {
        version: Number.isFinite(projectionVersion) && projectionVersion > 0 ? projectionVersion : 1,
        slots: {
            title: String(raw.slots?.title || 'entry.title'),
            subtitle: String(raw.slots?.subtitle || ''),
            summary: String(raw.slots?.summary || 'entry.excerpt'),
            date: String(raw.slots?.date || ''),
            image: String(raw.slots?.image || ''),
        },
        extras: normalizeItems(raw.extras),
        order: {
            field: String(raw.order?.field || ''),
            direction: ['asc', 'desc', 'upcoming'].includes(String(raw.order?.direction || 'desc').toLowerCase())
                ? String(raw.order?.direction || 'desc').toLowerCase()
                : 'desc',
            public: raw.order?.public === true || raw.order?.public === '1' || raw.order?.public === 'true',
        },
        filters: normalizeItems(raw.filters),
    };

    return {
        catalog: catalog && typeof catalog === 'object' ? catalog : {},
        projection,
        initialSource: String(initialSource || ''),
        initialCollection: String(initialCollection || ''),
        collection: '',
        source: '',
        slots: [
            { key: 'title', label: 'Título principal', hint: 'Prioridad 1', types: ['text', 'string'] },
            { key: 'subtitle', label: 'Antetítulo / subtítulo', hint: 'Prioridad 2', types: ['text', 'string', 'taxonomy'] },
            { key: 'summary', label: 'Resumen', hint: 'Lectura rápida', types: ['text', 'string'] },
            { key: 'date', label: 'Fecha o dato temporal', hint: 'Metadato', types: ['date', 'datetime', 'text', 'string'] },
            { key: 'image', label: 'Imagen', hint: 'Visual', types: ['media_reference'] },
        ],
        init() {
            this.syncContext(this.initialSource, this.initialCollection);
        },
        syncContext(source, collection) {
            this.source = String(source || '');
            this.collection = String(collection || '');
            if (this.projection.order.direction === 'upcoming' && !this.canUseUpcoming()) {
                this.projection.order.direction = 'desc';
            }
            const fields = this.fields();
            const valid = new Set(fields.map(field => field.value));
            if (!this.projection.slots.date && valid.has(this.projection.order.field)) {
                this.projection.slots.date = this.projection.order.field;
            }
            Object.keys(this.projection.slots).forEach(key => {
                if (this.projection.slots[key] && !valid.has(this.projection.slots[key])) this.projection.slots[key] = key === 'title' ? 'entry.title' : '';
            });
            this.projection.extras = this.projection.extras.filter(item => !item.source || valid.has(item.source));
            this.projection.filters = this.projection.filters.filter(item => !item.source || valid.has(item.source));
        },
        syncCollection(value) {
            this.syncContext('', value);
        },
        fields() {
            const key = ['event_items', 'catalog_items'].includes(this.source) ? this.source : this.collection;
            return Array.isArray(this.catalog[key]) ? this.catalog[key] : [];
        },
        canUseUpcoming() {
            if (this.source === 'cms_collection') {
                return true;
            }

            return this.source === 'auto'
                && this.collection !== ''
                && Array.isArray(this.catalog[this.collection]);
        },
        availableFields(criteria = {}) {
            return this.fields().filter(field => {
                if (criteria.sortable === true && !field.sortable) return false;
                if (criteria.filterable === true && !field.filterable) return false;
                if (Array.isArray(criteria.types) && !criteria.types.includes(field.type)) return false;
                return true;
            });
        },
        setSlot(key, value) {
            this.projection.slots[key] = String(value || '');
        },
        addExtra() {
            this.projection.extras.push({ id: `${Date.now()}-${Math.random().toString(36).slice(2)}`, source: '', label: '' });
        },
        removeExtra(index) {
            this.projection.extras.splice(index, 1);
        },
        addFilter() {
            this.projection.filters.push({ id: `${Date.now()}-${Math.random().toString(36).slice(2)}`, source: '', label: '', operator: 'equals' });
        },
        removeFilter(index) {
            this.projection.filters.splice(index, 1);
        },
        serialized() {
            return JSON.stringify(this.projection);
        },
    };
};


<?php

$listingFieldCatalog = is_array($listingFieldCatalog ?? null) ? $listingFieldCatalog : [];
$listingProjectionRaw = $submittedBlockConfig['listing_projection'] ?? ($blockConfig['listing_projection'] ?? []);
if (is_string($listingProjectionRaw)) {
    $listingProjectionRaw = json_decode($listingProjectionRaw, true);
}
$listingProjection = is_array($listingProjectionRaw) ? $listingProjectionRaw : [];
$listingProjection['slots'] = is_array($listingProjection['slots'] ?? null) ? $listingProjection['slots'] : [];
$listingProjection['order'] = is_array($listingProjection['order'] ?? null) ? $listingProjection['order'] : [];
$listingSourceType = trim((string) ($submittedBlockConfig['source_type'] ?? $blockConfig['source_type'] ?? ''));
$listingCollection = $submittedBlockConfig['collection_key'] ?? ($blockConfig['collection_key'] ?? ($submittedBlockConfig['collection_id'] ?? ($blockConfig['collection_id'] ?? '')));
// Read old configurations during the transition so the editor never appears
// empty while an instance is still served from a short-lived API cache.
$legacyDate = trim((string) ($submittedBlockConfig['date_field'] ?? $blockConfig['date_field'] ?? ''));
$legacyOrder = trim((string) ($submittedBlockConfig['order_by'] ?? $blockConfig['order_by'] ?? ''));
$catalogFields = array_merge(...array_values(array_filter($listingFieldCatalog, 'is_array')));
$findLegacyField = static function (string $legacy) use ($catalogFields): string {
    if ($legacy === '' || $legacy === 'auto') {
        return '';
    }
    $suffix = str_starts_with($legacy, 'listing.') ? substr($legacy, 8) : (str_starts_with($legacy, 'field:') ? substr($legacy, 6) : $legacy);
    foreach ($catalogFields as $field) {
        if (is_array($field) && (string) ($field['value'] ?? '') !== '' && str_ends_with((string) $field['value'], '.' . $suffix)) {
            return (string) $field['value'];
        }
    }
    return $legacy;
};
if (trim((string) ($listingProjection['slots']['date'] ?? '')) === '') {
    $listingProjection['slots']['date'] = $findLegacyField($legacyDate);
}
if (trim((string) ($listingProjection['order']['field'] ?? '')) === '') {
    $listingProjection['order']['field'] = $findLegacyField($legacyOrder);
}
$listingFieldCatalogJs = json_encode($listingFieldCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$listingProjectionJs = json_encode($listingProjection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$listingSourceTypeJs = json_encode($listingSourceType, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$listingCollectionJs = json_encode((string) $listingCollection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<div class="border-t border-gray-100 pt-5"
     x-data="listingProjectionEditor(<?= esc($listingFieldCatalogJs ?: '{}', 'attr') ?>, <?= esc($listingProjectionJs ?: '{}', 'attr') ?>, <?= esc($listingSourceTypeJs, 'attr') ?>, <?= esc($listingCollectionJs, 'attr') ?>)"
     @listing-source-changed.window="syncContext($event.detail.source, collectionKey !== '' ? collectionKey : collectionId)"
     x-effect="syncContext(sourceType !== '' ? sourceType : initialSource, collectionKey !== '' ? collectionKey : collectionId)">
    <input type="hidden" name="block_config[listing_projection]" :value="serialized()">

    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 space-y-5">
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-700">
                <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Contenido de la tarjeta</h4>
                <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-500">
                    Primero selecciona la fuente de contenido en la configuración del bloque. Después podrás elegir datos de esa fuente para el título, imagen, fechas, filtros y orden.
                </p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <template x-for="slot in slots" :key="slot.key">
                <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <span class="flex items-center justify-between gap-2 text-xs font-semibold text-slate-700">
                        <span x-text="slot.label"></span>
                        <span class="text-[10px] font-normal uppercase tracking-wider text-slate-400" x-text="slot.hint"></span>
                    </span>
                    <select class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                            :value="projection.slots[slot.key] || ''"
                            @change="setSlot(slot.key, $event.target.value)">
                        <option value="">— No mostrar —</option>
                        <template x-for="field in availableFields(slot)" :key="field.value">
                            <option :value="field.value" :selected="projection.slots[slot.key] === field.value" x-text="field.label"></option>
                        </template>
                    </select>
                </label>
            </template>
        </div>

        <div class="border-t border-slate-200 pt-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h5 class="text-xs font-semibold text-slate-800">Datos adicionales</h5>
                    <p class="mt-1 text-[11px] text-slate-500">Añade metadatos secundarios sin alterar la jerarquía principal de la tarjeta.</p>
                </div>
                <button type="button" @click="addExtra()" class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-white px-3 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-50">
                    <span aria-hidden="true">+</span> Añadir dato
                </button>
            </div>
            <div class="mt-3 space-y-2">
                <template x-for="(item, index) in projection.extras" :key="item.id">
                    <div class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:flex-row sm:items-center">
                        <select class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                :value="item.source"
                                @change="item.source = $event.target.value">
                            <option value="">— Seleccionar dato —</option>
                            <template x-for="field in availableFields({ types: ['text', 'date', 'number', 'taxonomy', 'string', 'select'] })" :key="field.value">
                                <option :value="field.value" x-text="field.label"></option>
                            </template>
                        </select>
                        <input type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:w-44" placeholder="Etiqueta opcional" x-model="item.label">
                        <button type="button" @click="removeExtra(index)" class="rounded-lg px-2 py-2 text-xs font-medium text-red-600 hover:bg-red-50">Quitar</button>
                    </div>
                </template>
                <p x-show="projection.extras.length === 0" class="rounded-xl border border-dashed border-slate-300 px-4 py-3 text-xs text-slate-500">No hay datos adicionales.</p>
            </div>
        </div>

        <div class="grid gap-4 border-t border-slate-200 pt-4 lg:grid-cols-2">
            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="text-xs font-semibold text-slate-700">Ordenar por</span>
                <select class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" :value="projection.order.field" @change="projection.order.field = $event.target.value">
                    <option value="">— Orden editorial por defecto —</option>
                    <template x-for="field in availableFields({ sortable: true })" :key="field.value">
                        <option :value="field.value" :selected="projection.order.field === field.value" x-text="field.label"></option>
                    </template>
                </select>
            </label>
            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="text-xs font-semibold text-slate-700">Dirección</span>
                <select class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" x-model="projection.order.direction">
                    <option value="asc">Ascendente · más antiguo / menor primero</option>
                    <option value="desc">Descendente · más reciente / mayor primero</option>
                    <option value="upcoming" x-show="canUseUpcoming()">Próximos primero · futuros ascendentes y pasados descendentes</option>
                </select>
            </label>
        </div>

        <label class="flex items-start gap-3 rounded-xl border border-brand-100 bg-brand-50/60 p-3">
            <input type="checkbox"
                   class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                   :checked="projection.order.public === true"
                   @change="projection.order.public = $event.target.checked">
            <span>
                <span class="block text-xs font-semibold text-slate-800">Permitir cambiar el orden en el sitio público</span>
                <span class="mt-1 block text-[11px] leading-relaxed text-slate-600">Si está desactivado, el sitio usará este orden por defecto sin mostrar controles adicionales a las visitas.</span>
            </span>
        </label>

        <div class="border-t border-slate-200 pt-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h5 class="text-xs font-semibold text-slate-800">Filtros públicos</h5>
                    <p class="mt-1 text-[11px] text-slate-500">Permite que la persona visitante filtre por datos concretos de esta colección.</p>
                </div>
                <button type="button" @click="addFilter()" class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-white px-3 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-50">
                    <span aria-hidden="true">+</span> Añadir filtro
                </button>
            </div>
            <div class="mt-3 space-y-2">
                <template x-for="(filter, index) in projection.filters" :key="filter.id">
                    <div class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:flex-row sm:items-center">
                        <select class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm" x-model="filter.source">
                            <option value="">— Seleccionar dato —</option>
                            <template x-for="field in availableFields({ filterable: true })" :key="field.value">
                                <option :value="field.value" x-text="field.label"></option>
                            </template>
                        </select>
                        <select class="rounded-lg border border-slate-300 px-3 py-2 text-sm" x-model="filter.operator">
                            <option value="equals">Igual a</option>
                            <option value="contains">Contiene</option>
                        </select>
                        <input type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:w-44" placeholder="Etiqueta del filtro" x-model="filter.label">
                        <button type="button" @click="removeFilter(index)" class="rounded-lg px-2 py-2 text-xs font-medium text-red-600 hover:bg-red-50">Quitar</button>
                    </div>
                </template>
                <p x-show="projection.filters.length === 0" class="rounded-xl border border-dashed border-slate-300 px-4 py-3 text-xs text-slate-500">No hay filtros públicos configurados.</p>
            </div>
        </div>
    </div>
</div>

<script>
window.listingProjectionEditor = window.listingProjectionEditor || function listingProjectionEditor(catalog, initial = {}, initialSource = '', initialCollection = '') {
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
            return this.source === 'cms_collection';
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
</script>

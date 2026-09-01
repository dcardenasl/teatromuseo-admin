<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

/**
 * Resolves the block type catalog augmented with dynamically-fetched select
 * options (active forms, collections, pages, entries) for config fields like
 * `form_key`, `collection_id`, `page_id`, `entry_id`.
 *
 * Extracted from BlockInstanceController (H-016,
 * docs/audits/2026-07-10-auditoria-profunda-robustez.md), which mixed this
 * schema/remote-call composition with HTTP request handling across ~300
 * lines. The controller now only calls resolve()/collectionsMap()/
 * entriesForCollection() and stays a thin Adapter.
 */
final class BlockTypeOptionsResolver
{
    private const RESOLVED_CACHE_KEY = 'cms_block_types_resolved_catalog';

    // Keep this aligned with BlockCatalogService's short TTL. The resolved
    // catalog is only used by the block create/edit screens, and the admin has
    // a manual refresh path for block-type schema edits.
    private const CACHE_TTL = 120;

    /** @var array<int, array{value: string, label: string}>|null */
    private ?array $pagesForIdsCache = null;

    /** @var array<int, array{value: string, label: string}>|null */
    private ?array $entriesForIdsCache = null;

    /** @var array<string, array{value: string, label: string}> */
    private array $entryReferenceOptionsCache = [];

    /**
     * Raw active-collections payload, fetched at most once per resolve()
     * call regardless of how many block types reference it (collection_key,
     * collection_id, entry_reference fields all share this).
     *
     * @var list<array<string, mixed>>|null
     */
    private ?array $activeCollectionsCache = null;

    /**
     * Active form keys, fetched at most once per resolve() call regardless
     * of how many form_embed block types are in the catalog.
     *
     * @var list<string>|null
     */
    private ?array $activeFormKeysCache = null;

    public function __construct(
        private readonly BlockCatalogServiceInterface $blockCatalogService,
        private readonly FormApiService $formApiService,
        private readonly CollectionApiService $collectionApiService,
        private readonly PageApiService $pageApiService,
        private readonly EntryApiService $entryApiService,
        private readonly ?CategoryApiService $categoryApiService = null,
    ) {
    }

    /**
     * The active block type catalog, each entry augmented in place with
     * dynamic select options where its schema declares form_key/collection_key/
     * collection_id/page_id/entry_id config fields.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolve(): array
    {
        $cachedItems = cache()->get(self::RESOLVED_CACHE_KEY);
        if (is_array($cachedItems)) {
            return $cachedItems;
        }

        $indexed = [];
        foreach ($this->blockCatalogService->indexed() as $id => $blockType) {
            if (! is_array($blockType)) {
                continue;
            }

            $this->injectDynamicFormOptions($blockType);
            $indexed[(int) $id] = $blockType;
        }

        cache()->save(self::RESOLVED_CACHE_KEY, $indexed, self::CACHE_TTL);

        return $indexed;
    }

    /**
     * The raw active block-type catalog with no dynamic option hydration —
     * for list/read-only views (block index, children list) that only
     * render static metadata (name, icon, block_key, category, description,
     * is_container) and never touch config_fields/schema_definition options.
     * Skips every forms/collections/pages/entries lookup that resolve()
     * performs, since none of that is rendered there. Callers that need
     * populated select options (create/edit forms) must use resolve() or
     * augment() instead.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rawIndexed(): array
    {
        return $this->blockCatalogService->indexed();
    }

    /**
     * Augments a single already-fetched block type in place — for callers
     * (e.g. the edit form) that load one block type by id instead of the
     * whole catalog via resolve().
     *
     * @param array<string, mixed> $blockType
     */
    public function augment(array &$blockType): void
    {
        $this->injectDynamicFormOptions($blockType);
    }

    /**
     * @return array<string, int> Map of collection_key => collection_id
     */
    public function collectionsMap(): array
    {
        $collectionsMap = [];
        foreach ($this->activeCollections() as $c) {
            if (! empty($c['collection_key']) && isset($c['id'])) {
                $collectionsMap[(string) $c['collection_key']] = (int) $c['id'];
            }
        }

        return $collectionsMap;
    }

    /**
     * Return the fields that a collection listing may safely project.
     *
     * The catalog is derived from the collection block template and the
     * canonical block schemas. It deliberately returns field references
     * instead of hardcoded listing options so new collections and block fields
     * become available to editors without another admin change.
     *
     * @return array<int|string, list<array{value: string, label: string, group: string, type: string, sortable: bool, filterable: bool}>>
     */
    public function listingFieldCatalog(): array
    {
        $catalog = [];
        $blockTypes = $this->blockCatalogService->indexed();

        // External sources do not have a CMS collection/template from which
        // to discover fields. They expose the same projection contract.
        $catalog['event_items'] = $this->eventListingFields();
        $catalog['catalog_items'] = $this->catalogListingFields();

        foreach ($this->activeCollections() as $collection) {
            $collectionId = (int) ($collection['id'] ?? 0);
            if ($collectionId <= 0) {
                continue;
            }

            $fields = [
                $this->listingField('entry.title', 'Título', 'Datos de la entrada', 'text', true, true),
                $this->listingField('entry.excerpt', 'Resumen', 'Datos de la entrada', 'text', false, true),
                $this->listingField('entry.slug', 'Slug', 'Datos de la entrada', 'text', true, true),
                $this->listingField('entry.featured_image', 'Imagen destacada', 'Datos de la entrada', 'media_reference', false, false),
                $this->listingField('entry.published_at', 'Fecha de publicación', 'Datos de la entrada', 'date', true, true),
                $this->listingField('entry.created_at', 'Fecha de creación', 'Datos de la entrada', 'date', true, true),
                $this->listingField('entry.sort_order', 'Orden editorial', 'Datos de la entrada', 'number', true, false),
                $this->listingField('taxonomy.categories', 'Categorías', 'Taxonomía', 'taxonomy', false, true),
                $this->listingField('taxonomy.tags', 'Etiquetas', 'Taxonomía', 'taxonomy', false, true),
            ];

            $template = $collection['block_template'] ?? [];
            if (is_string($template)) {
                $template = json_decode($template, true);
            }
            $templateBlocks = is_array($template) && is_array($template['blocks'] ?? null)
                ? $template['blocks']
                : [];

            foreach ($templateBlocks as $templateBlock) {
                if (! is_array($templateBlock)) {
                    continue;
                }
                $blockKey = trim((string) ($templateBlock['block_key'] ?? ''));
                $blockType = $this->findBlockType($blockTypes, $blockKey);
                if ($blockKey === '' || $blockType === null) {
                    continue;
                }
                $schema = $blockType['schema_definition'] ?? [];
                if (is_string($schema)) {
                    $schema = json_decode($schema, true);
                }
                $schemaFields = is_array($schema) && is_array($schema['fields'] ?? null)
                    ? $schema['fields']
                    : [];
                $blockLabel = (string) ($templateBlock['label'] ?? $blockType['name'] ?? $blockKey);

                foreach ($schemaFields as $fieldKey => $definition) {
                    if (! is_array($definition) || ! $this->isListingFieldType((string) ($definition['type'] ?? ''))) {
                        continue;
                    }
                    $fieldKey = trim((string) $fieldKey);
                    if ($fieldKey === '') {
                        continue;
                    }
                    $fieldType = (string) ($definition['type'] ?? 'string');
                    $fields[] = $this->listingField(
                        'block.' . $blockKey . '.' . $fieldKey,
                        $blockLabel . ' · ' . (string) ($definition['label'] ?? $fieldKey),
                        'Campos de bloques',
                        $fieldType,
                        in_array($fieldType, ['date', 'datetime', 'number', 'integer', 'string', 'text'], true),
                        in_array($fieldType, ['date', 'datetime', 'number', 'integer', 'string', 'text', 'select'], true),
                    );
                }
            }

            $catalog[$collectionId] = $fields;
            $collectionKey = trim((string) ($collection['collection_key'] ?? ''));
            if ($collectionKey !== '') {
                $catalog[$collectionKey] = $fields;
            }
        }

        return $catalog;
    }

    /**
     * Canonical projection fields exposed by the programming/event source.
     * The public source adapter translates these `entry.*` references to its
     * API query fields, keeping collection_list and collection_grid uniform.
     *
     * @return list<array{value: string, label: string, group: string, type: string, sortable: bool, filterable: bool}>
     */
    private function eventListingFields(): array
    {
        return [
            $this->listingField('entry.title', 'Título', 'Datos del evento', 'text', true, true),
            $this->listingField('entry.excerpt', 'Descripción', 'Datos del evento', 'text', false, true),
            $this->listingField('entry.slug', 'Slug', 'Datos del evento', 'text', true, true),
            $this->listingField('entry.event_type', 'Tipo de actividad', 'Datos del evento', 'select', true, true),
            $this->listingField('entry.start_time', 'Fecha y hora de inicio', 'Datos del evento', 'datetime', true, true),
            $this->listingField('entry.end_time', 'Fecha y hora de término', 'Datos del evento', 'datetime', true, true),
            $this->listingField('entry.venue', 'Lugar', 'Datos del evento', 'text', true, true),
            $this->listingField('entry.featured_image', 'Imagen de portada', 'Datos del evento', 'media_reference', false, false),
        ];
    }

    /** @return list<array{value: string, label: string, group: string, type: string, sortable: bool, filterable: bool}> */
    private function catalogListingFields(): array
    {
        return [
            $this->listingField('entry.title', 'Nombre', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.excerpt', 'Resumen', 'Datos de la pieza', 'text', false, true),
            $this->listingField('entry.slug', 'Slug', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.inventory_code', 'Código de inventario', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.origin', 'Origen', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.period', 'Período', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.creator', 'Autoría / creador', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.ubicacion', 'Ubicación', 'Datos de la pieza', 'text', true, true),
            $this->listingField('entry.materials', 'Materiales', 'Datos de la pieza', 'text', false, true),
            $this->listingField('entry.collection_number', 'Número de colección', 'Clasificación', 'text', true, true),
            $this->listingField('entry.collection_group', 'Grupo de colección', 'Clasificación', 'text', true, true),
            $this->listingField('taxonomy.categories', 'Categoría', 'Clasificación', 'taxonomy', false, true),
            $this->listingField('entry.created_at', 'Fecha de registro', 'Metadatos', 'date', true, true),
            $this->listingField('entry.updated_at', 'Última actualización', 'Metadatos', 'date', true, true),
            $this->listingField('entry.featured_image', 'Imagen de portada', 'Visual', 'media_reference', false, false),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $blockTypes
     * @return array<string, mixed>|null
     */
    private function findBlockType(array $blockTypes, string $blockKey): ?array
    {
        foreach ($blockTypes as $blockType) {
            if (is_array($blockType) && (string) ($blockType['block_key'] ?? '') === $blockKey) {
                return $blockType;
            }
        }

        return null;
    }

    private function isListingFieldType(string $type): bool
    {
        return in_array($type, ['string', 'text', 'textarea', 'richtext', 'date', 'datetime', 'number', 'integer', 'select', 'boolean', 'media_reference'], true);
    }

    /** @return array{value: string, label: string, group: string, type: string, sortable: bool, filterable: bool} */
    private function listingField(string $value, string $label, string $group, string $type, bool $sortable, bool $filterable): array
    {
        return compact('value', 'label', 'group', 'type', 'sortable', 'filterable');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function entriesForCollection(int $collectionId): array
    {
        if ($collectionId <= 0) {
            return [];
        }

        $options = [];
        try {
            $response = $this->safeApiCall(fn () => $this->entryApiService->list([
                'limit' => 250,
                'collection_id' => $collectionId,
            ]));

            if ($response['ok']) {
                foreach ($this->extractItems($response) as $item) {
                    $option = $this->toOption($item, ['title', 'name', 'slug']);
                    if ($option !== null) {
                        $options[] = $option;
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch entries for collection options: ' . $e->getMessage());
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $blockType
     */
    private function injectDynamicFormOptions(array &$blockType): void
    {
        $schema = is_array($blockType['schema_definition'] ?? [])
            ? ($blockType['schema_definition'] ?? [])
            : json_decode((string) ($blockType['schema_definition'] ?? '{}'), true);

        // Some domain API versions expose config fields both inside the
        // schema and at the top level. Merge them before resolving dynamic
        // options so a partial top-level projection cannot hide fields that
        // are present in the canonical schema (for example, hero_slider's
        // transition field).
        $schemaConfigFields = is_array($schema['config_fields'] ?? null)
            ? $schema['config_fields']
            : [];
        $projectedConfigFields = is_array($blockType['config_fields'] ?? null)
            ? $blockType['config_fields']
            : [];
        $mergedConfigFields = array_replace($schemaConfigFields, $projectedConfigFields);
        $schema['config_fields'] = $mergedConfigFields;
        $blockType['config_fields'] = $mergedConfigFields;
        $blockType['schema_definition'] = $schema;

        $hasFormEmbed     = ($blockType['block_key'] ?? '') === 'form_embed';
        $hasCollectionKey = isset($schema['config_fields']['collection_key']) || isset($blockType['config_fields']['collection_key']);
        $hasCollectionId  = isset($schema['config_fields']['collection_id'])  || isset($blockType['config_fields']['collection_id']);
        $hasCategoryId    = isset($schema['config_fields']['category_id']) || isset($blockType['config_fields']['category_id']);
        $hasPageId        = isset($schema['config_fields']['page_id']) || isset($blockType['config_fields']['page_id']);
        $hasEntryId       = isset($schema['config_fields']['entry_id']) || isset($blockType['config_fields']['entry_id']);
        $hasEntryReferences = false;

        $schemaFields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
        foreach ($schemaFields as $fieldKey => $fieldDefinition) {
            if (! is_array($fieldDefinition)) {
                continue;
            }
            if (in_array((string) ($fieldDefinition['type'] ?? ''), ['entry_reference', 'entry_reference_list'], true)) {
                $hasEntryReferences = true;
                $schema['fields'][$fieldKey]['options'] = $this->entryReferenceOptions($fieldDefinition);
            }
        }
        $blockType['fields'] = $schemaFields;

        if (! $hasFormEmbed && ! $hasCollectionKey && ! $hasCollectionId && ! $hasCategoryId && ! $hasPageId && ! $hasEntryId && ! $hasEntryReferences) {
            return;
        }

        if ($hasFormEmbed) {
            $forms = $this->activeFormKeys();

            if ($forms === []) {
                $forms = ['contact'];
            }

            if (isset($schema['config_fields']['form_key'])) {
                $schema['config_fields']['form_key']['type']    = 'select';
                $schema['config_fields']['form_key']['options'] = $forms;
            }

            if (isset($blockType['config_fields']['form_key'])) {
                $blockType['config_fields']['form_key']['type']    = 'select';
                $blockType['config_fields']['form_key']['options'] = $forms;
            }
        }

        if ($hasCollectionKey || $hasCollectionId) {
            $collectionsForKeys = [];
            $collectionsForIds  = [];
            foreach ($this->activeCollections() as $c) {
                if (! empty($c['collection_key'])) {
                    $collectionsForKeys[] = (string) $c['collection_key'];
                }
                if (isset($c['id'])) {
                    $label = $c['name'] ?? $c['collection_key'] ?? $c['title'] ?? $c['label'] ?? $c['id'];
                    $collectionsForIds[] = [
                        'value' => (int) $c['id'],
                        'label' => (string) $label,
                    ];
                }
            }

            if ($hasCollectionKey) {
                if (isset($schema['config_fields']['collection_key'])) {
                    $schema['config_fields']['collection_key']['type']    = 'select';
                    $schema['config_fields']['collection_key']['options'] = $collectionsForKeys;
                }
                if (isset($blockType['config_fields']['collection_key'])) {
                    $blockType['config_fields']['collection_key']['type']    = 'select';
                    $blockType['config_fields']['collection_key']['options'] = $collectionsForKeys;
                }
            }

            if ($hasCollectionId) {
                if (isset($schema['config_fields']['collection_id'])) {
                    $schema['config_fields']['collection_id']['type']    = 'select';
                    $schema['config_fields']['collection_id']['options'] = $collectionsForIds;
                }
                if (isset($blockType['config_fields']['collection_id'])) {
                    $blockType['config_fields']['collection_id']['type']    = 'select';
                    $blockType['config_fields']['collection_id']['options'] = $collectionsForIds;
                }
            }
        }

        if ($hasCategoryId) {
            $categoryOptions = $this->categoriesForIds();
            if (isset($schema['config_fields']['category_id'])) {
                $schema['config_fields']['category_id']['type'] = 'select';
                $schema['config_fields']['category_id']['options'] = $categoryOptions;
            }
            if (isset($blockType['config_fields']['category_id'])) {
                $blockType['config_fields']['category_id']['type'] = 'select';
                $blockType['config_fields']['category_id']['options'] = $categoryOptions;
            }
        }

        if ($hasPageId) {
            $pagesForIds = $this->pagesForIds();
            if (isset($schema['config_fields']['page_id'])) {
                $schema['config_fields']['page_id']['type']    = 'select';
                $schema['config_fields']['page_id']['options'] = $pagesForIds;
            }
            if (isset($blockType['config_fields']['page_id'])) {
                $blockType['config_fields']['page_id']['type']    = 'select';
                $blockType['config_fields']['page_id']['options'] = $pagesForIds;
            }
        }

        if ($hasEntryId) {
            $entriesForIds = $this->entriesForIds();
            if (isset($schema['config_fields']['entry_id'])) {
                $schema['config_fields']['entry_id']['type']    = 'select';
                $schema['config_fields']['entry_id']['options'] = $entriesForIds;
            }
            if (isset($blockType['config_fields']['entry_id'])) {
                $blockType['config_fields']['entry_id']['type']    = 'select';
                $blockType['config_fields']['entry_id']['options'] = $entriesForIds;
            }
        }

        $blockType['schema_definition'] = $schema;
    }

    /**
     * @param array<string, mixed> $fieldDefinition
     * @return array<int, array{value: string, label: string}>
     */
    private function entryReferenceOptions(array $fieldDefinition): array
    {
        $allowed = $fieldDefinition['collection_keys'] ?? $fieldDefinition['allowed_collections'] ?? [];
        if (isset($fieldDefinition['collection_key']) && is_string($fieldDefinition['collection_key'])) {
            $allowed = [$fieldDefinition['collection_key']];
        }
        if (! is_array($allowed)) {
            return [];
        }

        $collectionMap = $this->collectionsMap();
        $options = [];
        foreach ($allowed as $collectionKey) {
            $collectionKey = trim((string) $collectionKey);
            $collectionId = $collectionMap[$collectionKey] ?? null;
            if ($collectionKey === '' || $collectionId === null) {
                continue;
            }

            $cacheKey = $collectionKey . ':' . $collectionId;
            if (! isset($this->entryReferenceOptionsCache[$cacheKey])) {
                foreach ($this->entriesForCollection((int) $collectionId) as $option) {
                    $this->entryReferenceOptionsCache[$cacheKey . ':' . $option['value']] = [
                        'value' => $collectionKey . ':' . $option['value'],
                        'label' => $option['label'] . ' · ' . $collectionKey,
                    ];
                }
            }

            foreach ($this->entryReferenceOptionsCache as $optionKey => $option) {
                if (str_starts_with($optionKey, $cacheKey . ':')) {
                    $options[] = $option;
                }
            }
        }

        return $options;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function pagesForIds(): array
    {
        if ($this->pagesForIdsCache !== null) {
            return $this->pagesForIdsCache;
        }

        $pages = [];
        try {
            $response = $this->safeApiCall(fn () => $this->pageApiService->pages(['limit' => 250]));
            if ($response['ok']) {
                foreach ($this->extractItems($response) as $item) {
                    $option = $this->toOption($item, ['name', 'title', 'label']);
                    if ($option !== null) {
                        $pages[] = $option;
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch pages for options: ' . $e->getMessage());
        }

        return $this->pagesForIdsCache = $pages;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function entriesForIds(): array
    {
        if ($this->entriesForIdsCache !== null) {
            return $this->entriesForIdsCache;
        }

        $entries = [];
        try {
            $response = $this->safeApiCall(fn () => $this->entryApiService->list(['limit' => 250]));
            if ($response['ok']) {
                foreach ($this->extractItems($response) as $item) {
                    $option = $this->toOption($item, ['title', 'name', 'slug']);
                    if ($option !== null) {
                        $entries[] = $option;
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch entries for options: ' . $e->getMessage());
        }

        return $this->entriesForIdsCache = $entries;
    }

    /**
     * Category IDs are stable across locales, unlike translated category slugs.
     * Labels include the owning collection so an editor cannot accidentally
     * configure a category from a different collection without noticing it.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function categoriesForIds(): array
    {
        $categories = [];
        try {
            if ($this->categoryApiService === null) {
                return [];
            }
            $response = $this->safeApiCall(fn () => $this->categoryApiService->categories(['limit' => 500]));
            if ($response['ok']) {
                $collectionNames = [];
                foreach ($this->activeCollections() as $collection) {
                    $id = (int) ($collection['id'] ?? 0);
                    if ($id > 0) {
                        $collectionNames[$id] = (string) ($collection['name'] ?? $collection['collection_key'] ?? $id);
                    }
                }

                foreach ($this->extractItems($response) as $item) {
                    $id = (int) ($item['id'] ?? 0);
                    if ($id <= 0) {
                        continue;
                    }
                    $name = (string) ($item['name'] ?? $item['title'] ?? $item['slug'] ?? $id);
                    $collectionId = (int) ($item['collection_id'] ?? 0);
                    $collectionLabel = $collectionNames[$collectionId] ?? (string) ($item['collection_key'] ?? 'Colección');
                    $categories[] = ['value' => (string) $id, 'label' => $collectionLabel . ' · ' . $name];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch category options: ' . $e->getMessage());
        }

        return $categories;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function activeCollections(): array
    {
        if ($this->activeCollectionsCache !== null) {
            return $this->activeCollectionsCache;
        }

        $collections = [];
        try {
            $response = $this->safeApiCall(fn () => $this->collectionApiService->list(['limit' => 100, 'is_active' => true]));
            if ($response['ok']) {
                $collections = array_values(array_filter($this->extractItems($response), 'is_array'));
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch active collections: ' . $e->getMessage());
        }

        return $this->activeCollectionsCache = $collections;
    }

    /**
     * @return list<string>
     */
    private function activeFormKeys(): array
    {
        if ($this->activeFormKeysCache !== null) {
            return $this->activeFormKeysCache;
        }

        $forms = [];
        try {
            $response = $this->safeApiCall(fn () => $this->formApiService->list(['limit' => 100, 'is_active' => true]));
            if ($response['ok']) {
                foreach ($this->extractItems($response) as $f) {
                    if (is_array($f) && ! empty($f['form_key'])) {
                        $forms[] = (string) $f['form_key'];
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch forms for options: ' . $e->getMessage());
        }

        return $this->activeFormKeysCache = $forms;
    }

    /**
     * Builds a {value,label} select option from an item, preferring the
     * first non-empty title found in its translations, then falling back to
     * $fallbackKeys in order, then finally the item id.
     *
     * @param mixed $item
     * @param list<string> $fallbackKeys
     * @return array{value: string, label: string}|null
     */
    private function toOption(mixed $item, array $fallbackKeys): ?array
    {
        if (! is_array($item) || ! isset($item['id'])) {
            return null;
        }

        $label = null;
        if (! empty($item['translations']) && is_array($item['translations'])) {
            foreach ($item['translations'] as $translation) {
                if (is_array($translation) && ! empty($translation['title'])) {
                    $label = (string) $translation['title'];
                    break;
                }
            }
        }

        if ($label === null) {
            foreach ($fallbackKeys as $key) {
                if (! empty($item[$key])) {
                    $label = (string) $item[$key];
                    break;
                }
            }
        }

        return [
            'value' => (string) $item['id'],
            'label' => $label ?? (string) $item['id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function safeApiCall(callable $callback): array
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] API call failed: ' . $e->getMessage());

            return [
                'ok'          => false,
                'status'      => 0,
                'data'        => [],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [lang('App.connection_error')],
                'fieldErrors' => [],
            ];
        }
    }

    /**
     * @param array<string, mixed> $response
     * @return array<int, mixed>
     */
    private function extractItems(array $response): array
    {
        if (isset($response['ok']) && ! $response['ok']) {
            return [];
        }

        $payload = $response['data'] ?? [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        return is_array($payload) ? $payload : [];
    }
}

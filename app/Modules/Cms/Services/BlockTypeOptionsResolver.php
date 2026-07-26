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
    /** @var array<int, array{value: string, label: string}>|null */
    private ?array $pagesForIdsCache = null;

    /** @var array<int, array{value: string, label: string}>|null */
    private ?array $entriesForIdsCache = null;

    public function __construct(
        private readonly BlockCatalogServiceInterface $blockCatalogService,
        private readonly FormApiService $formApiService,
        private readonly CollectionApiService $collectionApiService,
        private readonly PageApiService $pageApiService,
        private readonly EntryApiService $entryApiService,
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
        $indexed = [];
        foreach ($this->blockCatalogService->indexed() as $id => $blockType) {
            if (! is_array($blockType)) {
                continue;
            }

            $this->injectDynamicFormOptions($blockType);
            $indexed[(int) $id] = $blockType;
        }

        return $indexed;
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
        try {
            $response = $this->safeApiCall(fn () => $this->collectionApiService->list(['limit' => 100, 'is_active' => true]));
            if ($response['ok']) {
                foreach ($this->extractItems($response) as $c) {
                    if (is_array($c) && ! empty($c['collection_key']) && isset($c['id'])) {
                        $collectionsMap[(string) $c['collection_key']] = (int) $c['id'];
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[BlockTypeOptionsResolver] Failed to fetch collections for map: ' . $e->getMessage());
        }

        return $collectionsMap;
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
        $hasPageId        = isset($schema['config_fields']['page_id']) || isset($blockType['config_fields']['page_id']);
        $hasEntryId       = isset($schema['config_fields']['entry_id']) || isset($blockType['config_fields']['entry_id']);

        if (! $hasFormEmbed && ! $hasCollectionKey && ! $hasCollectionId && ! $hasPageId && ! $hasEntryId) {
            return;
        }

        if ($hasFormEmbed) {
            $forms = [];
            try {
                $formsResponse = $this->safeApiCall(
                    fn () => $this->formApiService->list(['limit' => 100, 'is_active' => true])
                );
                if ($formsResponse['ok']) {
                    foreach ($this->extractItems($formsResponse) as $f) {
                        if (is_array($f) && ! empty($f['form_key'])) {
                            $forms[] = (string) $f['form_key'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', '[BlockTypeOptionsResolver] Failed to fetch forms for options: ' . $e->getMessage());
            }

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
            try {
                $collectionsResponse = $this->safeApiCall(
                    fn () => $this->collectionApiService->list(['limit' => 100, 'is_active' => true])
                );
                if ($collectionsResponse['ok']) {
                    foreach ($this->extractItems($collectionsResponse) as $c) {
                        if (! is_array($c)) {
                            continue;
                        }
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
                }
            } catch (\Throwable $e) {
                log_message('error', '[BlockTypeOptionsResolver] Failed to fetch collections for options: ' . $e->getMessage());
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

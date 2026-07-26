<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

final class BlockCatalogService implements BlockCatalogServiceInterface
{
    private const CACHE_KEY = 'cms_block_types_active_catalog';
    private const TEMPLATE_CACHE_KEY = 'cms_block_types_template_catalog';

    // Kept short on purpose: block type schemas can change from outside the
    // admin's own edit form (a domain migration or seed, a direct API call),
    // which this cache has no way to be notified about. A 1h TTL left content
    // editors staring at stale/wrong block forms for up to an hour with no
    // visible cause — see BlockTypeController::refreshCache() for the manual
    // escape hatch and audits/2026-07-16 for the incident that prompted this.
    private const CACHE_TTL = 120;

    public function __construct(
        private readonly BlockTypeApiService $blockTypeService
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $cachedItems = cache()->get(self::CACHE_KEY);
        if (is_array($cachedItems)) {
            return $cachedItems;
        }

        try {
            $response = $this->blockTypeService->list(['limit' => 100, 'is_active' => true]);
        } catch (\Throwable $exception) {
            log_message('warning', 'Block catalog unavailable: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return [];
        }

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        $items = $this->extractItems($response);
        $items = array_values(array_filter($items, static fn (array $item): bool => self::isActive($item)));
        usort($items, static function (array $a, array $b): int {
            $orderA = (int) ($a['sort_order'] ?? 0);
            $orderB = (int) ($b['sort_order'] ?? 0);
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            $nameA = strtolower((string) ($a['name'] ?? ''));
            $nameB = strtolower((string) ($b['name'] ?? ''));
            if ($nameA !== $nameB) {
                return $nameA <=> $nameB;
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        cache()->save(self::CACHE_KEY, $items, self::CACHE_TTL);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function indexed(): array
    {
        $indexed = [];
        foreach ($this->all() as $type) {
            if (isset($type['id'])) {
                $indexed[(int) $type['id']] = $type;
            }
        }

        return $indexed;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function templates(): array
    {
        $cachedItems = cache()->get(self::TEMPLATE_CACHE_KEY);
        if (is_array($cachedItems)) {
            return $cachedItems;
        }

        try {
            $items = $this->blockTypeService->templates();
        } catch (\Throwable $exception) {
            log_message('warning', 'Block template catalog unavailable: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return [];
        }

        cache()->save(self::TEMPLATE_CACHE_KEY, $items, self::CACHE_TTL);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableForEntries(): array
    {
        return $this->filterSelectable($this->all(), static function (array $item): bool {
            return self::supportsEntries($item) && ! self::isChildOnly($item);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableTopLevel(): array
    {
        $items = $this->all();
        $childOnlyKeys = $this->childOnlyKeys($items);

        return $this->filterSelectable($items, static function (array $item) use ($childOnlyKeys): bool {
            $blockKey = (string) ($item['block_key'] ?? '');

            return ! self::isChildOnly($item) && ! in_array($blockKey, $childOnlyKeys, true);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableForPages(): array
    {
        return $this->filterSelectable($this->all(), static function (array $item): bool {
            return self::supportsPages($item) && ! self::isChildOnly($item);
        });
    }

    /**
     * @param array<string, mixed> $response
     * @return array<int, array<string, mixed>>
     */
    private function extractItems(array $response): array
    {
        $payload = $response['data'] ?? [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        return is_array($payload) ? array_values($payload) : [];
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isActive(array $item): bool
    {
        $value = $item['is_active'] ?? false;

        return $value === true || $value === 1 || $value === '1';
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param \Closure(array<string, mixed>): bool $filter
     * @return array<int, array<string, mixed>>
     */
    private function filterSelectable(array $items, \Closure $filter): array
    {
        return array_values(array_filter($items, $filter));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, string>
     */
    private function childOnlyKeys(array $items): array
    {
        $containerAllowedChildren = [];
        foreach ($items as $item) {
            if ((string) ($item['block_key'] ?? '') !== 'container') {
                continue;
            }

            $schema = $this->schemaDefinition($item);
            $containerAllowedChildren = array_values(array_filter(
                $schema['allowed_children'] ?? [],
                static fn ($childKey): bool => is_string($childKey) && $childKey !== ''
            ));
            break;
        }

        if ($containerAllowedChildren === []) {
            return [];
        }

        $allChildrenKeys = [];
        foreach ($items as $item) {
            $schema = $this->schemaDefinition($item);
            $allowed = $schema['allowed_children'] ?? [];
            foreach ($allowed as $childKey) {
                if (is_string($childKey) && $childKey !== '') {
                    $allChildrenKeys[] = $childKey;
                }
            }
        }

        $allChildrenKeys = array_values(array_unique($allChildrenKeys));

        return array_values(array_diff($allChildrenKeys, $containerAllowedChildren));
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function schemaDefinition(array $item): array
    {
        $schema = $item['schema_definition'] ?? [];
        if (is_array($schema)) {
            return $schema;
        }

        if (! is_string($schema) || trim($schema) === '') {
            return [];
        }

        $decoded = json_decode($schema, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function supportsEntries(array $item): bool
    {
        $value = $item['supports_entries'] ?? true;

        return $value !== false && $value !== 0 && $value !== '0';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function supportsPages(array $item): bool
    {
        $value = $item['supports_pages'] ?? true;

        return $value !== false && $value !== 0 && $value !== '0';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isChildOnly(array $item): bool
    {
        $value = $item['is_child_only'] ?? false;

        return $value === true || $value === 1 || $value === '1';
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\BlockInstanceApiService;
use App\Modules\Cms\Services\LanguageApiService;

final class PagePresetApplier
{
    public function __construct(
        private readonly BlockCatalogServiceInterface $blockCatalogService,
        private readonly BlockInstanceApiService $blockInstanceService,
        private readonly LanguageApiService $languageService
    ) {
    }

    public static function fromServices(): self
    {
        return new self(
            service('blockCatalogService'),
            service('blockInstanceApiService'),
            service('languageApiService')
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function apply(int $pageId, string $pageType, array $context = []): void
    {
        $preset = CmsPresetCatalog::resolvePage($pageType);
        $blocks = $preset['block_template']['blocks'] ?? [];
        if (! is_array($blocks) || $blocks === []) {
            return;
        }

        $existing = $this->blockInstanceService->list($pageId, 'page');
        if (($existing['ok'] ?? false) && $this->extractItems($existing) !== []) {
            return;
        }

        $indexedTypes = $this->blockCatalogService->indexed();
        $languages = $this->activeLanguages();

        foreach ($blocks as $blockDef) {
            if (! is_array($blockDef)) {
                continue;
            }

            $blockKey = (string) ($blockDef['block_key'] ?? '');
            if ($blockKey === '') {
                continue;
            }

            $blockType = $this->blockTypeByKey($indexedTypes, $blockKey);
            if ($blockType === null) {
                continue;
            }

            $payload = [
                'block_id' => (int) ($blockType['id'] ?? 0),
                'parent_instance_id' => null,
                'sort_order' => (int) ($blockDef['sort_order'] ?? 1),
                'is_active' => true,
                'block_config' => $this->resolveBlockConfigDefaults($blockDef, $context),
                'translations' => $this->blankTranslations($languages),
            ];

            $this->blockInstanceService->create($pageId, 'page', $payload);
        }
    }

    /**
     * @param array<string, mixed> $blockDef
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function resolveBlockConfigDefaults(array $blockDef, array $context): array
    {
        $defaults = $blockDef['block_config_defaults'] ?? new \stdClass();
        if (is_object($defaults)) {
            $defaults = json_decode(json_encode($defaults, JSON_THROW_ON_ERROR), true) ?: [];
        }
        if (! is_array($defaults)) {
            $defaults = [];
        }

        $blockKey = (string) ($blockDef['block_key'] ?? '');
        if ($blockKey === 'collection_listing') {
            $collectionId = isset($context['collection_id']) ? (int) $context['collection_id'] : 0;
            $defaults['collection_id'] = $collectionId > 0 ? $collectionId : (int) ($defaults['collection_id'] ?? 0);
        }

        return $defaults;
    }

    /**
     * @param array<int, array<string, mixed>> $indexedTypes
     * @return array<string, mixed>|null
     */
    private function blockTypeByKey(array $indexedTypes, string $blockKey): ?array
    {
        foreach ($indexedTypes as $type) {
            if (! is_array($type)) {
                continue;
            }

            if ((string) ($type['block_key'] ?? '') === $blockKey) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activeLanguages(): array
    {
        $response = $this->languageService->list(['limit' => 100, 'is_active' => true]);
        return $this->extractItems($response);
    }

    /**
     * @param array<int, array<string, mixed>> $languages
     * @return array<int, array<string, mixed>>
     */
    private function blankTranslations(array $languages): array
    {
        $translations = [];
        foreach ($languages as $language) {
            $languageId = (int) ($language['id'] ?? 0);
            if ($languageId <= 0) {
                continue;
            }

            $translations[] = [
                'language_id' => $languageId,
                'block_data' => [],
                'is_published' => true,
            ];
        }

        return $translations;
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
}

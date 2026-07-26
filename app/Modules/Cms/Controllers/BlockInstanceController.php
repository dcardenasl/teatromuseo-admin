<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Libraries\Cms\CmsEnums;
use App\Modules\Cms\Services\BlockInstanceApiService;
use App\Modules\Cms\Services\BlockTypeOptionsResolver;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Cms\Support\BlockOwnerRouting;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BlockInstanceController extends BaseWebController
{
    protected BlockInstanceApiService $blockInstanceService;
    protected BlockTypeOptionsResolver $blockTypeOptions;
    protected TranslationAuditApiService $translationAuditService;

    private const OWNER_PAGE = 'page';
    private const OWNER_ENTRY = 'entry';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockInstanceService = service('blockInstanceApiService');
        $this->blockTypeOptions = service('blockTypeOptionsResolver');
        $this->translationAuditService = service('translationAuditApiService');
    }

    private function requireWrite(): ?RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $permission = $ownerType === self::OWNER_ENTRY ? 'cms.entries.write' : 'cms.pages.write';
        if (! has_permission($permission)) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    private function ownerTypeFromRequest(): string
    {
        $segments = service('request')->getUri()->getSegments();

        return in_array('entries', $segments, true) ? self::OWNER_ENTRY : self::OWNER_PAGE;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchOwner(string $ownerType, string $ownerId): array
    {
        $response = $ownerType === self::OWNER_ENTRY
            ? $this->safeApiCall(fn () => service('entryApiService')->get($ownerId))
            : $this->safeApiCall(fn () => service('pageApiService')->get($ownerId));

        return $response['ok'] ? $this->extractData($response) : [];
    }

    /**
     * Translation status for every block belonging to a single page/entry,
     * keyed by instance id -> language code -> {language_id,status,detail}.
     * Backs the contextual per-language badges rendered in the block list
     * views; degrades to empty on API failure so a badge outage never breaks
     * the block builder itself (same graceful-degradation posture as
     * activeLanguages() above).
     *
     * @return array<int|string, array<string, array<string, mixed>>>
     */
    private function ownerBlockTranslationStatus(string $ownerType, string $ownerId): array
    {
        $response = $this->safeApiCall(fn () => $this->translationAuditService->auditOwnerBlocks($ownerType, $ownerId));
        $data = $response['ok'] ? $this->extractData($response) : [];

        return is_array($data['blocks'] ?? null) ? $data['blocks'] : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activeLanguages(): array
    {
        $languages = cache()->get('cms_active_languages');
        if (is_array($languages)) {
            return array_values($languages);
        }

        $languagesResponse = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        $languages = $languagesResponse['ok'] ? $this->extractItems($languagesResponse) : [];
        if (! empty($languages)) {
            cache()->save('cms_active_languages', $languages, 3600);
        }

        return array_values($languages);
    }

    /**
     * @param array<string, mixed> $blockType
     * @return array<string, mixed>
     */
    private function blockSchemaFields(array $blockType): array
    {
        $schemaDefinition = $blockType['schema_definition'] ?? [];
        if (is_string($schemaDefinition) && trim($schemaDefinition) !== '') {
            $decoded = json_decode($schemaDefinition, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $schemaDefinition = $decoded;
            }
        }

        if (! is_array($schemaDefinition)) {
            return [];
        }

        $fields = $schemaDefinition['fields'] ?? [];
        return is_array($fields) ? $fields : [];
    }

    /**
     * @param array<string, mixed> $blockType
     */
    private function shouldSeedBlankTranslations(array $blockType): bool
    {
        return $this->blockSchemaFields($blockType) === [];
    }

    public function index(string $ownerId): string|RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }
        $routes = BlockOwnerRouting::routes($ownerType);

        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, $ownerType));
        $allBlocks = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];

        // Only show top-level blocks in the page editor (children managed via their parent's UI)
        $blocks = array_values(array_filter($allBlocks, static fn (array $b) => empty($b['parent_instance_id'])));

        $typesIndexed = $this->blockTypeOptions->resolve();
        $routes = BlockOwnerRouting::routes($ownerType);
        $previewUrl = BlockOwnerRouting::previewUrl($ownerType, $page, $this->activeLanguages());

        return $this->render('cms/pages/blocks/index', [
            'title'             => lang('Pages.blocks_section_title') . ': ' . ($page['title'] ?? BlockOwnerRouting::label($ownerType)),
            'page'              => $page,
            'blocks'            => $blocks,
            'blockTypes'        => $typesIndexed,
            'collectionsMap'    => $this->blockTypeOptions->collectionsMap(),
            'publicSiteUrl'     => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
            'languages'         => $this->activeLanguages(),
            'blockTranslationStatus'  => $this->ownerBlockTranslationStatus($ownerType, $ownerId),
            'ownerType'         => $ownerType,
            'ownerLabel'        => BlockOwnerRouting::label($ownerType),
            'ownerShowRoute'    => BlockOwnerRouting::showRoute($ownerType),
            'ownerBlocksRoute'   => $routes['index'],
            'ownerCreateRoute'   => $routes['create'],
            'ownerStoreRoute'    => $routes['store'],
            'ownerEditRoute'     => $routes['edit'],
            'ownerUpdateRoute'   => $routes['update'],
            'ownerDeleteRoute'   => $routes['delete'],
            'ownerChildrenRoute' => $routes['children'],
            'ownerReorderRoute'  => $routes['reorder'],
            'ownerChildrenReorderRoute' => $routes['childrenReorder'],
            'showPreview'        => $previewUrl !== '',
            'previewUrl'         => $previewUrl,
        ]);
    }

    public function create(string $ownerId): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $typesIndexed = $this->blockTypeOptions->resolve();
        $types = array_values($typesIndexed);

        $languages = $this->activeLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        $parentIdRaw      = $this->request->getGet('parent_instance_id');
        $parentInstanceId = ($parentIdRaw !== null && is_scalar($parentIdRaw) && (int) $parentIdRaw > 0)
            ? (int) $parentIdRaw
            : null;

        $parentBlockType = null;
        if ($parentInstanceId !== null) {
            $parentResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, (string) $parentInstanceId));
            if ($parentResponse['ok']) {
                $parentBlock = $this->extractData($parentResponse);
                $parentBlockId = $parentBlock['block_id'] ?? null;
                if ($parentBlockId) {
                    $parentBlockType = $typesIndexed[$parentBlockId] ?? null;
                }
            }
        }

        $routes = BlockOwnerRouting::routes($ownerType);

        return $this->render('cms/pages/blocks/create', [
            'title'             => $parentInstanceId !== null
                ? lang('Pages.blocks_add') . ' ' . BlockOwnerRouting::childLabel($ownerType)
                : lang('Pages.block_add_title'),
            'page'              => $page,
            'blockTypes'        => $types,
            'languages'         => $languages,
            'entryOptionsUrl'   => route_to('admin.cms.blocks.entries'),
            'translateUrl'      => route_to('admin.cms.translate'),
            'defaultLangCode'   => $languageContext['defaultLangCode'],
            'defaultLangId'     => $languageContext['defaultLangId'],
            'parentInstanceId'  => $parentInstanceId,
            'parentBlockType'   => $parentBlockType,
            'ownerType'         => $ownerType,
            'ownerLabel'        => BlockOwnerRouting::label($ownerType),
            'ownerBlocksRoute'   => $routes['index'],
            'ownerCreateRoute'   => $routes['create'],
            'ownerStoreRoute'    => $routes['store'],
            'ownerChildrenRoute' => $routes['children'],
            'ownerChildLabel'    => BlockOwnerRouting::childLabel($ownerType),
        ]);
    }

    public function store(string $ownerId): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $blockIdRaw    = $this->request->getPost('block_id');
        $sortOrderRaw  = $this->request->getPost('sort_order');
        $isActiveRaw   = $this->request->getPost('is_active');
        $blockId       = is_scalar($blockIdRaw) ? (int) $blockIdRaw : 0;
        $sortOrder     = is_scalar($sortOrderRaw) ? (int) $sortOrderRaw : 0;
        $isActive      = ! empty($isActiveRaw);

        // Parse config: accepts array (schema-driven form inputs) or JSON string
        $blockConfigRaw = $this->request->getPost('block_config');
        $blockConfig = [];
        if (is_array($blockConfigRaw)) {
            $blockConfig = $blockConfigRaw;
        } elseif (is_string($blockConfigRaw) && trim($blockConfigRaw) !== '') {
            $decoded = json_decode($blockConfigRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blockConfig = $decoded;
            }
        }

        // Process translations: normalize to drop blank translation rows
        $translationsRaw = $this->request->getPost('translations');
        $translations = [];
        foreach (is_array($translationsRaw) ? $translationsRaw : [] as $t) {
            $langId = (int) ($t['language_id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }

            $blockData = $t['block_data'] ?? [];
            // Check if there is actual content in the block data
            $hasData = false;
            foreach ($blockData as $val) {
                if (is_string($val) && trim($val) !== '') {
                    $hasData = true;
                    break;
                }
                if (is_array($val) && !empty($val)) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $translations[] = [
                    'language_id'  => $langId,
                    'block_data'   => $blockData,
                    'is_published' => (bool) ($t['is_published'] ?? true)
                ];
            }
        }

        if ($translations === []) {
            $typeResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->get($blockId));
            if ($typeResponse['ok']) {
                $blockType = $this->extractData($typeResponse);
                if ($this->shouldSeedBlankTranslations($blockType)) {
                    $translations = array_map(
                        static fn (array $language): array => [
                            'language_id'  => (int) ($language['id'] ?? 0),
                            'block_data'   => [],
                            'is_published' => true,
                        ],
                        $this->activeLanguages()
                    );

                    $translations = array_values(array_filter(
                        $translations,
                        static fn (array $translation): bool => $translation['language_id'] > 0
                    ));
                }
            }
        }

        $parentIdRaw       = $this->request->getPost('parent_instance_id');
        $parentInstanceId  = ($parentIdRaw !== null && is_scalar($parentIdRaw) && (int) $parentIdRaw > 0)
            ? (int) $parentIdRaw
            : null;

        $ownerType = $this->ownerTypeFromRequest();
        $payload = [
            'block_id'           => $blockId,
            'owner_type'         => $ownerType,
            'owner_id'           => (int) $ownerId,
            'parent_instance_id' => $parentInstanceId,
            'sort_order'         => $sortOrder,
            'is_active'          => $isActive,
            'block_config'       => $blockConfig,
            'translations'       => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->create($ownerId, $ownerType, $payload));

        if (!$response['ok']) {
            return $this->failApi($response, lang('Pages.block_add_failed'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', lang('Pages.child_added_success', [BlockOwnerRouting::childLabel($ownerType)]));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('success', lang('Pages.block_added_success'));
    }

    public function edit(string $ownerId, string $id): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
        if (!$blockResponse['ok']) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Pages.block_not_found'));
        }
        $block = $this->extractData($blockResponse);

        $typeCacheKey = 'cms_block_type_' . $block['block_id'];
        $blockType = cache()->get($typeCacheKey);
        if ($blockType === null) {
            $typeResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->get($block['block_id']));
            $blockType = $typeResponse['ok'] ? $this->extractData($typeResponse) : [];
            if (! empty($blockType)) {
                // Short TTL on purpose — see BlockCatalogService::CACHE_TTL for why.
                cache()->save($typeCacheKey, $blockType, 120);
            }
        }
        $this->blockTypeOptions->augment($blockType);

        // Fetch active languages (cached)
        $languages = cache()->get('cms_active_languages');
        if ($languages === null) {
            $languagesResponse = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
            $languages = $languagesResponse['ok'] ? $this->extractItems($languagesResponse) : [];
            if (! empty($languages)) {
                cache()->save('cms_active_languages', $languages, 3600);
            }
        }

        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];

        // Extract translatable fields (exclude non-translatable types)
        $allFields = is_array($blockType['fields'] ?? null) ? $blockType['fields'] : [];
        $translatableFieldNames = [];
        foreach ($allFields as $fieldKey => $field) {
            $fieldType = $field['type'] ?? 'string';
            if (!in_array($fieldType, CmsEnums::NON_TRANSLATABLE_TYPES, true)) {
                $translatableFieldNames[] = "block_data][{$fieldKey}";
            }
        }

        // Build translation targets using the centralized method
        $translateTargets = ($defaultLangId > 0 && !empty($translatableFieldNames))
            ? $this->buildTranslateTargets($languages, $translatableFieldNames, $defaultLangId, 'translations')
            : [];

        // Per-language completeness for this single block, keyed by language
        // code (e.g. 'es' => ['status' => 'incomplete', ...]) — powers the
        // status dot on each ES/EN/FR tab. Degrades to [] on API failure so a
        // status outage never blocks editing the block itself.
        $blockStatusResponse = $this->safeApiCall(fn () => $this->translationAuditService->auditResource('block_instance', $id));
        $blockTranslationStatus = $blockStatusResponse['ok'] ? $this->extractData($blockStatusResponse) : [];

        return $this->render('cms/pages/blocks/edit', [
            'title'        => lang('Pages.block_edit_title'),
            'page'         => $page,
            'block'        => $block,
            'blockType'    => $blockType,
            'languages'    => $languages,
            'entryOptionsUrl' => route_to('admin.cms.blocks.entries'),
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'focusLangId'  => $focusLangId,
            'blockTranslationStatus' => $blockTranslationStatus,
            'ownerType'    => $ownerType,
            'ownerLabel'   => BlockOwnerRouting::label($ownerType),
            'ownerBlocksRoute' => BlockOwnerRouting::routes($ownerType)['index'],
            'ownerStoreRoute' => BlockOwnerRouting::routes($ownerType)['store'],
            'ownerEditRoute' => BlockOwnerRouting::routes($ownerType)['edit'],
            'ownerUpdateRoute' => BlockOwnerRouting::routes($ownerType)['update'],
            'ownerDeleteRoute' => BlockOwnerRouting::routes($ownerType)['delete'],
            'ownerChildrenRoute' => BlockOwnerRouting::routes($ownerType)['children'],
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function update(string $ownerId, string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $blockIdRaw   = $this->request->getPost('block_id');
        $sortOrderRaw = $this->request->getPost('sort_order');
        $isActiveRaw  = $this->request->getPost('is_active');
        $blockId      = is_scalar($blockIdRaw) ? (int) $blockIdRaw : 0;
        $sortOrder    = is_scalar($sortOrderRaw) ? (int) $sortOrderRaw : 0;
        $isActive     = ! empty($isActiveRaw);

        // Preserve parent_instance_id from the existing block record
        $ownerType        = $this->ownerTypeFromRequest();
        $existingBlock    = $this->extractData($this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id)));
        $parentInstanceId = !empty($existingBlock['parent_instance_id']) ? (int) $existingBlock['parent_instance_id'] : null;

        $blockConfigRaw = $this->request->getPost('block_config');
        $blockConfig = [];
        if (is_array($blockConfigRaw)) {
            $blockConfig = $blockConfigRaw;
        } elseif (is_string($blockConfigRaw) && trim($blockConfigRaw) !== '') {
            $decoded = json_decode($blockConfigRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blockConfig = $decoded;
            }
        }

        $translationsRaw = $this->request->getPost('translations');
        $translations = [];
        foreach (is_array($translationsRaw) ? $translationsRaw : [] as $t) {
            $langId = (int) ($t['language_id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }

            $blockData = $t['block_data'] ?? [];
            $hasData = false;
            foreach ($blockData as $val) {
                if (is_string($val) && trim($val) !== '') {
                    $hasData = true;
                    break;
                }
                if (is_array($val) && !empty($val)) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $translations[] = [
                    'language_id'  => $langId,
                    'block_data'   => $blockData,
                    'is_published' => (bool) ($t['is_published'] ?? true)
                ];
            }
        }

        $payload = [
            'block_id'           => $blockId,
            'owner_type'         => $ownerType,
            'owner_id'           => (int) $ownerId,
            'parent_instance_id' => $parentInstanceId,
            'sort_order'         => $sortOrder,
            'is_active'          => $isActive,
            'block_config'       => $blockConfig,
            'translations'       => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $id, $payload));

        if (!$response['ok']) {
            return $this->failApi($response, lang('Pages.block_update_failed'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to($this->resolveReturnUrl(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId)))->with('success', lang('Pages.child_updated_success', [BlockOwnerRouting::childLabel($ownerType)]));
        }

        return redirect()->to($this->resolveReturnUrl(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId)))->with('success', lang('Pages.block_updated_success'));
    }

    public function delete(string $ownerId, string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        // Fetch before deleting so we know where to redirect
        $ownerType        = $this->ownerTypeFromRequest();
        $blockResponse    = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
        $block            = ($blockResponse['ok'] ?? false) ? $this->extractData($blockResponse) : [];
        $parentInstanceId = !empty($block['parent_instance_id']) ? (int) $block['parent_instance_id'] : null;

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->delete($ownerId, $ownerType, $id));

        if (!$response['ok']) {
            if ($parentInstanceId !== null) {
                return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('error', lang('Pages.block_delete_failed'));
            }
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Pages.block_delete_failed'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', lang('Pages.child_deleted_success', [BlockOwnerRouting::childLabel($ownerType)]));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('success', lang('Pages.block_deleted_success'));
    }

    public function reorder(string $ownerId): RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ordersRaw = $this->request->getPost('orders');
        $orders    = is_array($ordersRaw) ? $ordersRaw : [];

        $ownerType = $this->ownerTypeFromRequest();
        $failed    = [];

        foreach ($orders as $id => $order) {
            $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
            if (!$blockResponse['ok']) {
                $failed[] = $id;
                continue;
            }

            $block          = $this->extractData($blockResponse);
            $updateResponse = $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $id, [
                'block_id'     => (int) $block['block_id'],
                'owner_type'   => $ownerType,
                'owner_id'     => (int) $ownerId,
                'sort_order'   => (int) $order,
                'is_active'    => (bool) ($block['is_active'] ?? true),
                'block_config' => $block['block_config'] ?? [],
                'translations' => $block['translations'] ?? []
            ]));

            if (!$updateResponse['ok']) {
                $failed[] = $id;
            }
        }

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response
                ->setStatusCode($failed === [] ? 200 : 422)
                ->setContentType('application/json')
                ->setBody(json_encode(['ok' => $failed === [], 'failed' => $failed]) ?: '{}');
        }

        if ($failed !== []) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Pages.blocks_reorder_error'));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('success', lang('Pages.blocks_reorder_success'));
    }

    public function reorderChildren(string $ownerId, string $instanceId): RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ordersRaw = $this->request->getPost('orders');
        $orders    = is_array($ordersRaw) ? $ordersRaw : [];

        $ownerType = $this->ownerTypeFromRequest();
        $failed    = [];

        foreach ($orders as $id => $order) {
            $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
            if (!$blockResponse['ok']) {
                $failed[] = $id;
                continue;
            }

            $block          = $this->extractData($blockResponse);
            $updateResponse = $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $id, [
                'block_id'           => (int) $block['block_id'],
                'owner_type'         => $ownerType,
                'owner_id'           => (int) $ownerId,
                'parent_instance_id' => (int) $instanceId,
                'sort_order'         => (int) $order,
                'is_active'          => (bool) ($block['is_active'] ?? true),
                'block_config'       => $block['block_config'] ?? [],
                'translations'       => $block['translations'] ?? [],
            ]));

            if (!$updateResponse['ok']) {
                $failed[] = $id;
            }
        }

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response
                ->setStatusCode($failed === [] ? 200 : 422)
                ->setContentType('application/json')
                ->setBody(json_encode(['ok' => $failed === [], 'failed' => $failed]) ?: '{}');
        }

        if ($failed !== []) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, $instanceId))->with('error', lang('Pages.child_reorder_error'));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, $instanceId))->with('success', lang('Pages.child_reorder_success'));
    }

    public function entryOptions(): ResponseInterface
    {
        if (! has_permission('cms.entries.read')) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $collectionIdRaw = $this->request->getGet('collection_id');
        $collectionId = is_scalar($collectionIdRaw) ? (int) $collectionIdRaw : 0;
        if ($collectionId <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setContentType('application/json')
                ->setBody(json_encode(['ok' => false, 'message' => 'collection_id is required']) ?: '{}');
        }

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode([
                'ok' => true,
                'options' => $this->blockTypeOptions->entriesForCollection($collectionId),
            ]) ?: '{}');
    }

    public function children(string $ownerId, string $instanceId): string|RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $parentResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $instanceId));
        if (!$parentResponse['ok']) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Pages.block_not_found'));
        }
        $parentBlock = $this->extractData($parentResponse);

        // Fetch all page blocks and filter to just children of this instance
        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, $ownerType));
        $allBlocks      = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];
        $children       = array_values(array_filter($allBlocks, static fn (array $b) => (int) ($b['parent_instance_id'] ?? 0) === (int) $instanceId));

        $typesIndexed = $this->blockTypeOptions->resolve();

        $parentType = $typesIndexed[$parentBlock['block_id']] ?? [];

        return $this->render('cms/pages/blocks/children/index', [
            'title'                => BlockOwnerRouting::childLabel($ownerType) . ': ' . ($parentType['name'] ?? BlockOwnerRouting::label($ownerType)),
            'page'                 => $page,
            'parentBlock'          => $parentBlock,
            'parentType'           => $parentType,
            'children'             => $children,
            'blockTypes'           => $typesIndexed,
            'collectionsMap'       => $this->blockTypeOptions->collectionsMap(),
            'languages'            => $this->activeLanguages(),
            'blockTranslationStatus' => $this->ownerBlockTranslationStatus($ownerType, $ownerId),
            'ownerType'            => $ownerType,
            'ownerLabel'           => BlockOwnerRouting::label($ownerType),
            'ownerBlocksRoute'     => BlockOwnerRouting::routes($ownerType)['index'],
            'ownerCreateRoute'     => BlockOwnerRouting::routes($ownerType)['create'],
            'ownerStoreRoute'      => BlockOwnerRouting::routes($ownerType)['store'],
            'ownerEditRoute'       => BlockOwnerRouting::routes($ownerType)['edit'],
            'ownerUpdateRoute'     => BlockOwnerRouting::routes($ownerType)['update'],
            'ownerDeleteRoute'     => BlockOwnerRouting::routes($ownerType)['delete'],
            'ownerChildrenReorderRoute' => BlockOwnerRouting::routes($ownerType)['childrenReorder'],
            'childLabel'           => BlockOwnerRouting::childLabel($ownerType),
        ]);
    }
}

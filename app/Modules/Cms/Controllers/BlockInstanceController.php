<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\BlockInstanceApiService;
use App\Modules\Cms\Services\BlockTypeOptionsResolver;
use App\Modules\Cms\Services\CmsWorkspaceBffAdapter;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Cms\Support\BlockOwnerRouting;
use App\Modules\Files\Services\FileApiService;
use App\Support\CmsFieldEnums;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BlockInstanceController extends BaseWebController
{
    private const OWNER_CACHE_TTL = 120;

    protected BlockInstanceApiService $blockInstanceService;
    protected FileApiService $fileService;
    protected BlockTypeOptionsResolver $blockTypeOptions;
    protected TranslationAuditApiService $translationAuditService;
    protected CmsWorkspaceBffAdapter $cmsWorkspace;

    private const OWNER_PAGE = 'page';
    private const OWNER_ENTRY = 'entry';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockInstanceService = service('blockInstanceApiService');
        $this->fileService = service('fileApiService');
        $this->blockTypeOptions = service('blockTypeOptionsResolver');
        $this->translationAuditService = service('translationAuditApiService');
        $this->cmsWorkspace = service('cmsWorkspaceBffAdapter');
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

    /** @return array<string, mixed> */
    private function fetchOwner(string $ownerType, string $ownerId): array
    {
        $cacheKey = 'cms_block_owner_' . $ownerType . '_' . $ownerId;
        $cachedOwner = cache()->get($cacheKey);
        if (is_array($cachedOwner)) {
            return $cachedOwner;
        }

        $response = $ownerType === self::OWNER_ENTRY
            ? $this->safeApiCall(fn () => service('entryApiService')->get($ownerId))
            : $this->safeApiCall(fn () => service('pageApiService')->get($ownerId));

        $owner = $response['ok'] ? $this->extractData($response) : [];
        if ($owner !== []) {
            cache()->save($cacheKey, $owner, self::OWNER_CACHE_TTL);
        }

        return $owner;
    }

    /** @return array<int|string, array<string, array<string, mixed>>> */
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

    /**
     * @param array<int, array<string, mixed>> $children
     * @return array<int, array<string, mixed>>
     */
    private function enrichChildImageThumbnails(array $children): array
    {
        $fileIds = [];
        foreach ($children as $child) {
            $config = is_array($child['block_config'] ?? null) ? $child['block_config'] : [];
            foreach (['photo', 'hover_photo'] as $field) {
                $reference = is_array($config[$field] ?? null) ? $config[$field] : [];
                $fileId = (int) ($reference['file_id'] ?? 0);
                if ($fileId > 0) {
                    $fileIds[$fileId] = $fileId;
                }
            }
        }

        if ($fileIds === []) {
            return $children;
        }

        $manifestResponse = $this->safeApiCall(fn () => $this->fileService->pickerManifest());
        if (! $manifestResponse['ok']) {
            return $children;
        }

        $manifest = $this->extractData($manifestResponse);
        $items = is_array($manifest['items'] ?? null) ? $manifest['items'] : [];
        $thumbnailUrls = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $fileId = (int) ($item['id'] ?? 0);
            $thumbUrl = trim((string) ($item['preview_url'] ?? ''));
            if ($fileId > 0 && $thumbUrl !== '' && isset($fileIds[$fileId])) {
                $thumbnailUrls[$fileId] = $thumbUrl;
            }
        }

        foreach ($children as &$child) {
            $config = is_array($child['block_config'] ?? null) ? $child['block_config'] : [];
            foreach (['photo', 'hover_photo'] as $field) {
                $reference = is_array($config[$field] ?? null) ? $config[$field] : [];
                $fileId = (int) ($reference['file_id'] ?? 0);
                if (isset($thumbnailUrls[$fileId])) {
                    $reference['thumb_url'] = $thumbnailUrls[$fileId];
                    $config[$field] = $reference;
                }
            }
            $child['block_config'] = $config;
        }
        unset($child);

        return $children;
    }

    public function index(string $ownerId): string|RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $workspace = $ownerType === self::OWNER_PAGE
            ? $this->workspaceForOwner((int) $ownerId)
            : null;
        if ($ownerType === self::OWNER_PAGE || $workspace !== null) {
            if ($workspace === null || ! is_array($workspace[$ownerType] ?? null)) {
                return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', $this->workspaceError($ownerType));
            }

            $page = $workspace[$ownerType];
            $allBlocks = is_array($workspace['blocks'] ?? null) ? $workspace['blocks'] : [];
            $blocks = array_values(array_filter($allBlocks, static fn (array $b): bool => empty($b['parent_instance_id'])));
            $routes = BlockOwnerRouting::routes($ownerType);
            $languages = is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [];
            $previewUrl = BlockOwnerRouting::previewUrl($ownerType, $page, $languages);

            return $this->render('cms/pages/blocks/index', [
                'title'             => lang('Blocks.blocks_section_title') . ': ' . ($page['title'] ?? BlockOwnerRouting::label($ownerType)),
                'page'              => $page,
                'blocks'            => $blocks,
                'blockTypes'        => (array) ($workspace['blockTypes'] ?? []),
                'collectionsMap'    => (array) ($workspace['collectionsMap'] ?? []),
                'publicSiteUrl'     => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
                'languages'         => $languages,
                'blockTranslationStatus' => (array) ($workspace['blockTranslationStatus'] ?? []),
                'ownerType'         => $ownerType,
                'ownerLabel'        => BlockOwnerRouting::label($ownerType),
                'ownerShowRoute'    => BlockOwnerRouting::showRoute($ownerType),
                'ownerBlocksRoute'  => $routes['index'],
                'ownerCreateRoute'  => $routes['create'],
                'ownerStoreRoute'   => $routes['store'],
                'ownerEditRoute'    => $routes['edit'],
                'ownerUpdateRoute'  => $routes['update'],
                'ownerDeleteRoute'  => $routes['delete'],
                'ownerChildrenRoute' => $routes['children'],
                'ownerReorderRoute' => $routes['reorder'],
                'ownerChildrenReorderRoute' => $routes['childrenReorder'],
                'showPreview'       => $previewUrl !== '',
                'previewUrl'        => $previewUrl,
            ]);
        }

        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, $ownerType));
        $allBlocks = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];
        $blocks = array_values(array_filter($allBlocks, static fn (array $b) => empty($b['parent_instance_id'])));
        $typesIndexed = $this->blockTypeOptions->rawIndexed();
        $routes = BlockOwnerRouting::routes($ownerType);
        $languages = $this->activeLanguages();
        $previewUrl = BlockOwnerRouting::previewUrl($ownerType, $page, $languages);

        return $this->render('cms/pages/blocks/index', [
            'title'             => lang('Blocks.blocks_section_title') . ': ' . ($page['title'] ?? BlockOwnerRouting::label($ownerType)),
            'page'              => $page,
            'blocks'            => $blocks,
            'blockTypes'        => $typesIndexed,
            'collectionsMap'    => $this->blockTypeOptions->collectionsMap(),
            'publicSiteUrl'     => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
            'languages'         => $languages,
            'blockTranslationStatus' => $this->ownerBlockTranslationStatus($ownerType, $ownerId),
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
        $parentIdRaw      = $this->request->getGet('parent_instance_id');
        $parentInstanceId = ($parentIdRaw !== null && is_scalar($parentIdRaw) && (int) $parentIdRaw > 0)
            ? (int) $parentIdRaw
            : null;

        $workspace = $ownerType === self::OWNER_PAGE
            ? $this->workspaceForOwner((int) $ownerId, $parentInstanceId)
            : null;
        if ($ownerType === self::OWNER_PAGE || $workspace !== null) {
            if ($workspace === null || ! is_array($workspace[$ownerType] ?? null)) {
                return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', $this->workspaceError($ownerType));
            }

            $page = $workspace[$ownerType];
            $types = array_values(is_array($workspace['blockTypes'] ?? null) ? $workspace['blockTypes'] : []);
            $languages = is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [];
            $languageContext = $this->resolveLanguageContext($languages);
            $routes = BlockOwnerRouting::routes($ownerType);
            $parentBlockType = $parentInstanceId !== null && is_array($workspace['blockType'] ?? null)
                ? $workspace['blockType']
                : null;

            return $this->render('cms/pages/blocks/create', [
                'title'             => $parentInstanceId !== null
                    ? lang('Blocks.blocks_add') . ' ' . BlockOwnerRouting::childLabel($ownerType)
                    : lang('Blocks.block_add_title'),
                'page'              => $page,
                'blockTypes'        => $types,
                'languages'         => $languages,
                'entryOptionsUrl'   => route_to('admin.cms.blocks.entries'),
                'listingFieldCatalog' => (array) ($workspace['listingFieldCatalog'] ?? []),
                'translateUrl'      => route_to('admin.cms.translate'),
                'defaultLangCode'    => $languageContext['defaultLangCode'],
                'defaultLangId'      => $languageContext['defaultLangId'],
                'defaultLangIndex'   => $languageContext['defaultLangIndex'],
                'parentInstanceId'   => $parentInstanceId,
                'parentBlockType'    => $parentBlockType,
                'ownerType'          => $ownerType,
                'ownerLabel'        => BlockOwnerRouting::label($ownerType),
                'ownerBlocksRoute'   => $routes['index'],
                'ownerCreateRoute'   => $routes['create'],
                'ownerChildrenRoute' => $routes['children'],
                'ownerChildLabel'    => BlockOwnerRouting::childLabel($ownerType),
            ]);
        }

        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $typesIndexed = $this->blockTypeOptions->resolve();
        $types = array_values($typesIndexed);
        $languages = $this->activeLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $parentBlockType = null;
        if ($parentInstanceId !== null) {
            $parentResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, (string) $parentInstanceId));
            if ($parentResponse['ok']) {
                $parentBlock = $this->extractData($parentResponse);
                $parentBlockType = $typesIndexed[$parentBlock['block_id'] ?? 0] ?? null;
            }
        }

        $routes = BlockOwnerRouting::routes($ownerType);

        return $this->render('cms/pages/blocks/create', [
            'title'             => $parentInstanceId !== null
                ? lang('Blocks.blocks_add') . ' ' . BlockOwnerRouting::childLabel($ownerType)
                : lang('Blocks.block_add_title'),
            'page'              => $page,
            'blockTypes'        => $types,
            'languages'         => $languages,
            'entryOptionsUrl'   => route_to('admin.cms.blocks.entries'),
            'listingFieldCatalog' => $types !== [] ? $this->blockTypeOptions->listingFieldCatalog() : [],
            'translateUrl'      => route_to('admin.cms.translate'),
            'defaultLangCode'   => $languageContext['defaultLangCode'],
            'defaultLangId'     => $languageContext['defaultLangId'],
            'defaultLangIndex'  => $languageContext['defaultLangIndex'],
            'parentInstanceId'  => $parentInstanceId,
            'parentBlockType'   => $parentBlockType,
            'ownerType'         => $ownerType,
            'ownerLabel'        => BlockOwnerRouting::label($ownerType),
            'ownerBlocksRoute'  => $routes['index'],
            'ownerCreateRoute'  => $routes['create'],
            'ownerStoreRoute'   => $routes['store'],
            'ownerChildrenRoute' => $routes['children'],
            'ownerChildLabel'   => BlockOwnerRouting::childLabel($ownerType),
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
            return $this->failApi($response, lang('Blocks.block_add_failed'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', lang('Blocks.child_added_success', [BlockOwnerRouting::childLabel($ownerType)]));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('success', lang('Blocks.block_added_success'));
    }

    public function edit(string $ownerId, string $id): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ownerType = $this->ownerTypeFromRequest();
        $workspace = $ownerType === self::OWNER_PAGE
            ? $this->workspaceForOwner((int) $ownerId, (int) $id)
            : null;
        if ($ownerType === self::OWNER_PAGE || $workspace !== null) {
            if ($workspace === null) {
                return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', $this->workspaceError($ownerType));
            }
            if (! is_array($workspace[$ownerType] ?? null) || ! is_array($workspace['block'] ?? null)) {
                return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Blocks.block_not_found'));
            }

            $page = $workspace[$ownerType];
            $block = $workspace['block'];
            $blockType = is_array($workspace['blockType'] ?? null) ? $workspace['blockType'] : [];
            $languages = is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [];
            $languageContext = $this->resolveLanguageContext($languages);
            $allFields = is_array($blockType['fields'] ?? null) ? $blockType['fields'] : [];
            $translatableFieldNames = [];
            foreach ($allFields as $fieldKey => $field) {
                $fieldType = is_array($field) ? ($field['type'] ?? 'string') : 'string';
                if (! in_array($fieldType, CmsFieldEnums::NON_TRANSLATABLE_TYPES, true)) {
                    $translatableFieldNames[] = "block_data][{$fieldKey}";
                }
            }
            $translateTargets = ($languageContext['defaultLangId'] > 0 && $translatableFieldNames !== [])
                ? $this->buildTranslateTargets($languages, $translatableFieldNames, $languageContext['defaultLangId'], 'translations')
                : [];
            $routes = BlockOwnerRouting::routes($ownerType);
            $focusLangRaw = $this->request->getGet('focus_lang');
            $focusLangId = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
                ? (int) $focusLangRaw
                : 0;

            return $this->render('cms/pages/blocks/edit', [
                'title' => lang('Blocks.block_edit_title'),
                'page' => $page,
                'block' => $block,
                'blockType' => $blockType,
                'languages' => $languages,
                'entryOptionsUrl' => route_to('admin.cms.blocks.entries'),
                'listingFieldCatalog' => (array) ($workspace['listingFieldCatalog'] ?? []),
                'defaultLangId' => $languageContext['defaultLangId'],
                'defaultLangCode' => $languageContext['defaultLangCode'],
                'defaultLangIndex' => $languageContext['defaultLangIndex'],
                'translateTargets' => $translateTargets,
                'focusLangId' => $focusLangId,
                'blockTranslationStatus' => (array) ($workspace['blockTranslationStatus'] ?? []),
                'ownerType' => $ownerType,
                'ownerLabel' => BlockOwnerRouting::label($ownerType),
                'ownerBlocksRoute' => $routes['index'],
                'ownerStoreRoute' => $routes['store'],
                'ownerEditRoute' => $routes['edit'],
                'ownerUpdateRoute' => $routes['update'],
                'ownerDeleteRoute' => $routes['delete'],
                'ownerChildrenRoute' => $routes['children'],
                'returnTo' => $this->incomingReturnTo(),
            ]);
        }

        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;
        $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
        if (! $blockResponse['ok']) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Blocks.block_not_found'));
        }
        $block = $this->extractData($blockResponse);
        $typeCacheKey = 'cms_block_type_' . $block['block_id'];
        $blockType = cache()->get($typeCacheKey);
        if ($blockType === null) {
            $typeResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->get($block['block_id']));
            $blockType = $typeResponse['ok'] ? $this->extractData($typeResponse) : [];
            if ($blockType !== []) {
                cache()->save($typeCacheKey, $blockType, 120);
            }
        }
        $this->blockTypeOptions->augment($blockType);

        $languages = cache()->get('cms_active_languages');
        if ($languages === null) {
            $languagesResponse = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
            $languages = $languagesResponse['ok'] ? $this->extractItems($languagesResponse) : [];
            if ($languages !== []) {
                cache()->save('cms_active_languages', $languages, 3600);
            }
        }

        $languageContext = $this->resolveLanguageContext($languages);
        $translatableFieldNames = [];
        foreach (is_array($blockType['fields'] ?? null) ? $blockType['fields'] : [] as $fieldKey => $field) {
            $fieldType = is_array($field) ? ($field['type'] ?? 'string') : 'string';
            if (! in_array($fieldType, CmsFieldEnums::NON_TRANSLATABLE_TYPES, true)) {
                $translatableFieldNames[] = "block_data][{$fieldKey}";
            }
        }
        $translateTargets = ($languageContext['defaultLangId'] > 0 && $translatableFieldNames !== [])
            ? $this->buildTranslateTargets($languages, $translatableFieldNames, $languageContext['defaultLangId'], 'translations')
            : [];
        $blockStatusResponse = $this->safeApiCall(fn () => $this->translationAuditService->auditResource('block_instance', $id));

        return $this->render('cms/pages/blocks/edit', [
            'title' => lang('Blocks.block_edit_title'),
            'page' => $page,
            'block' => $block,
            'blockType' => $blockType,
            'languages' => $languages,
            'entryOptionsUrl' => route_to('admin.cms.blocks.entries'),
            'listingFieldCatalog' => $this->blockTypeOptions->listingFieldCatalog(),
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'focusLangId' => $focusLangId,
            'blockTranslationStatus' => $blockStatusResponse['ok'] ? $this->extractData($blockStatusResponse) : [],
            'ownerType' => $ownerType,
            'ownerLabel' => BlockOwnerRouting::label($ownerType),
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

        // The edit form already carries parent_instance_id. Reading the block
        // again before every PUT creates an avoidable CMS request and holds a
        // second PHP process open on the hosting plan.
        $ownerType        = $this->ownerTypeFromRequest();
        $parentIdRaw      = $this->request->getPost('parent_instance_id');
        $parentInstanceId = is_scalar($parentIdRaw) && (int) $parentIdRaw > 0 ? (int) $parentIdRaw : null;

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
            return $this->failApi($response, lang('Blocks.block_update_failed'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to($this->resolveReturnUrl(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId)))->with('success', lang('Blocks.child_updated_success', [BlockOwnerRouting::childLabel($ownerType)]));
        }

        return redirect()->to($this->resolveReturnUrl(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId)))->with('success', lang('Blocks.block_updated_success'));
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
                return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('error', lang('Blocks.block_delete_failed'));
            }
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Blocks.block_delete_failed'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', lang('Blocks.child_deleted_success', [BlockOwnerRouting::childLabel($ownerType)]));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('success', lang('Blocks.block_deleted_success'));
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
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Blocks.blocks_reorder_error'));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('success', lang('Blocks.blocks_reorder_success'));
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
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, $instanceId))->with('error', lang('Blocks.child_reorder_error'));
        }

        return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['children'], $ownerId, $instanceId))->with('success', lang('Blocks.child_reorder_success'));
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
        $workspace = $ownerType === self::OWNER_PAGE
            ? $this->workspaceForOwner((int) $ownerId, (int) $instanceId)
            : null;
        if ($ownerType === self::OWNER_PAGE || $workspace !== null) {
            // The workspace projection names the requested instance `block`.
            // `parentBlock` is only present when that instance itself has a
            // parent. For the children screen the requested instance is the
            // parent, so accept the canonical `block` section as the parent.
            $parentBlock = is_array($workspace) && is_array($workspace['parentBlock'] ?? null)
                ? $workspace['parentBlock']
                : (is_array($workspace) && is_array($workspace['block'] ?? null) ? $workspace['block'] : null);

            if ($workspace === null) {
                return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', $this->workspaceError($ownerType));
            }
            if (! is_array($workspace[$ownerType] ?? null) || ! is_array($parentBlock)) {
                return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Blocks.block_not_found'));
            }

            $page = $workspace[$ownerType];
            $typesIndexed = is_array($workspace['blockTypes'] ?? null) ? $workspace['blockTypes'] : [];
            $parentType = is_array($workspace['parentType'] ?? null)
                ? $workspace['parentType']
                : (is_array($workspace['blockType'] ?? null) ? $workspace['blockType'] : []);
            $children = is_array($workspace['children'] ?? null) ? $workspace['children'] : [];
            $allowedChildren = is_array($parentType['allowed_children'] ?? null)
                ? $parentType['allowed_children']
                : (is_array($parentType['schema_definition']['allowed_children'] ?? null) ? $parentType['schema_definition']['allowed_children'] : []);
            $childType = [];
            foreach ($typesIndexed as $type) {
                if (is_array($type) && in_array((string) ($type['block_key'] ?? ''), $allowedChildren, true)) {
                    $childType = $type;
                    break;
                }
            }
            $childLabel = (string) ($childType['name'] ?? BlockOwnerRouting::childLabel($ownerType));
            $childLabelPlural = (string) ($childType['block_key'] ?? '') === 'team_member'
                ? 'Miembros del equipo'
                : $childLabel . 's';
            $routes = BlockOwnerRouting::routes($ownerType);

            return $this->render('cms/pages/blocks/children/index', [
                'title' => BlockOwnerRouting::childLabel($ownerType) . ': ' . ($parentType['name'] ?? BlockOwnerRouting::label($ownerType)),
                'page' => $page,
                'parentBlock' => $parentBlock,
                'parentType' => $parentType,
                'children' => $children,
                'blockTypes' => $typesIndexed,
                'collectionsMap' => (array) ($workspace['collectionsMap'] ?? []),
                'languages' => (array) ($workspace['languages'] ?? []),
                'blockTranslationStatus' => (array) ($workspace['blockTranslationStatus'] ?? []),
                'ownerType' => $ownerType,
                'ownerLabel' => BlockOwnerRouting::label($ownerType),
                'ownerBlocksRoute' => $routes['index'],
                'ownerCreateRoute' => $routes['create'],
                'ownerStoreRoute' => $routes['store'],
                'ownerEditRoute' => $routes['edit'],
                'ownerUpdateRoute' => $routes['update'],
                'ownerDeleteRoute' => $routes['delete'],
                'ownerChildrenReorderRoute' => $routes['childrenReorder'],
                'childLabel' => $childLabel,
                'childLabelPlural' => $childLabelPlural,
            ]);
        }

        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to(BlockOwnerRouting::listRoute($ownerType))->with('error', BlockOwnerRouting::notFoundMessage($ownerType));
        }

        $parentResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $instanceId));
        if (! $parentResponse['ok']) {
            return redirect()->to(route_to(BlockOwnerRouting::routes($ownerType)['index'], $ownerId))->with('error', lang('Blocks.block_not_found'));
        }
        $parentBlock = $this->extractData($parentResponse);
        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, $ownerType));
        $allBlocks = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];
        $children = array_values(array_filter($allBlocks, static fn (array $b): bool => (int) ($b['parent_instance_id'] ?? 0) === (int) $instanceId));
        $children = $this->enrichChildImageThumbnails($children);
        $typesIndexed = $this->blockTypeOptions->rawIndexed();
        $parentType = $typesIndexed[$parentBlock['block_id']] ?? [];
        $schema = is_array($parentType['schema_definition'] ?? null)
            ? $parentType['schema_definition']
            : json_decode((string) ($parentType['schema_definition'] ?? '{}'), true);
        $allowedChildren = is_array($parentType['allowed_children'] ?? null)
            ? $parentType['allowed_children']
            : (is_array($schema) && is_array($schema['allowed_children'] ?? null) ? $schema['allowed_children'] : []);
        $childType = [];
        foreach ($typesIndexed as $type) {
            if (is_array($type) && in_array((string) ($type['block_key'] ?? ''), $allowedChildren, true)) {
                $childType = $type;
                break;
            }
        }
        $childLabel = (string) ($childType['name'] ?? BlockOwnerRouting::childLabel($ownerType));
        $childLabelPlural = (string) ($childType['block_key'] ?? '') === 'team_member'
            ? 'Miembros del equipo'
            : $childLabel . 's';

        return $this->render('cms/pages/blocks/children/index', [
            'title' => BlockOwnerRouting::childLabel($ownerType) . ': ' . ($parentType['name'] ?? BlockOwnerRouting::label($ownerType)),
            'page' => $page,
            'parentBlock' => $parentBlock,
            'parentType' => $parentType,
            'children' => $children,
            'blockTypes' => $typesIndexed,
            'collectionsMap' => $this->blockTypeOptions->collectionsMap(),
            'languages' => $this->activeLanguages(),
            'blockTranslationStatus' => $this->ownerBlockTranslationStatus($ownerType, $ownerId),
            'ownerType' => $ownerType,
            'ownerLabel' => BlockOwnerRouting::label($ownerType),
            'ownerBlocksRoute' => BlockOwnerRouting::routes($ownerType)['index'],
            'ownerCreateRoute' => BlockOwnerRouting::routes($ownerType)['create'],
            'ownerStoreRoute' => BlockOwnerRouting::routes($ownerType)['store'],
            'ownerEditRoute' => BlockOwnerRouting::routes($ownerType)['edit'],
            'ownerUpdateRoute' => BlockOwnerRouting::routes($ownerType)['update'],
            'ownerDeleteRoute' => BlockOwnerRouting::routes($ownerType)['delete'],
            'ownerChildrenReorderRoute' => BlockOwnerRouting::routes($ownerType)['childrenReorder'],
            'childLabel' => $childLabel,
            'childLabelPlural' => $childLabelPlural,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function workspaceForOwner(int $ownerId, ?int $instanceId = null): ?array
    {
        return $this->cmsWorkspace->page($ownerId, $instanceId);
    }

    private function workspaceError(string $ownerType): string
    {
        return $this->cmsWorkspace->wasUnavailable()
            ? lang('App.connection_error')
            : BlockOwnerRouting::notFoundMessage($ownerType);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\BlockInstanceApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\MenuApiService;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Files\Services\FileApiService;
use App\Support\FileSizeLimits;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class WizardController extends BaseWebController
{
    protected BlockInstanceApiService $blockInstanceService;
    protected MenuApiService $menuService;
    protected EntryApiService $entryService;
    protected FileApiService $fileService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockInstanceService = service('blockInstanceApiService');
        $this->menuService = service('menuApiService');
        $this->entryService = service('entryApiService');
        $this->fileService = service('fileApiService');
    }

    public function index(): string
    {
        return $this->render('cms/wizard/index', [
            'title'     => lang('Wizard.title'),
            'csrfName'  => csrf_token(),
            'csrfToken' => csrf_hash(),
        ]);
    }

    public function config(): ResponseInterface
    {
        $domainClient = service('domainApiClient');

        $wizardResult = $this->safeApiCall(static fn () => $domainClient->get('/cms/wizard/config'));
        if (isset($wizardResult['ok']) && $wizardResult['ok'] === false) {
            return $this->response
                ->setStatusCode(502)
                ->setJSON(['ok' => false, 'message' => 'Could not load wizard config from domain API']);
        }

        $config = $this->extractData($wizardResult);

        // Enrich block_types with id, is_container, allowed_children, icon, category
        $blockTypesResult = $this->safeApiCall(
            static fn () => $domainClient->get('/cms/block-types', ['limit' => 200, 'is_active' => 1])
        );

        if (! isset($blockTypesResult['ok']) || $blockTypesResult['ok'] !== false) {
            $btRaw  = $this->extractData($blockTypesResult);
            $btList = $btRaw['items'] ?? $btRaw['data'] ?? [];

            foreach ($btList as $bt) {
                $key = $bt['block_key'] ?? null;
                if (! $key) {
                    continue;
                }

                $schemaDef                   = $bt['schema_definition'] ?? [];
                $existing                    = $config['block_types'][$key] ?? [];
                $config['block_types'][$key] = array_merge($existing, [
                    'id'               => $bt['id'] ?? null,
                    'icon'             => $bt['icon'] ?? null,
                    'category'         => $bt['category'] ?? null,
                    'is_container'     => (bool) ($bt['is_container'] ?? false),
                    'supports_pages'   => (bool) ($bt['supports_pages'] ?? true),
                    'supports_entries' => (bool) ($bt['supports_entries'] ?? false),
                    'allowed_children' => $schemaDef['allowed_children'] ?? [],
                ]);
            }
        }

        $config['collection_types'] = CmsPresetCatalog::collectionTypeOptions();
        $config['page_types'] = CmsPresetCatalog::pageTypeOptions();
        $config['field_primitives'] = $config['field_primitives'] ?? [
            'text',
            'textarea',
            'richtext',
            'image',
            'file',
            'url',
            'number',
            'boolean',
            'select',
            'date',
            'datetime',
        ];
        $config['block_capabilities'] = $config['block_capabilities'] ?? [];
        $config['setup_state'] = array_merge([
            'has_languages' => ! empty($config['languages']),
            'has_collections' => ! empty($config['collections']),
            'has_active_block_types' => ! empty($config['block_types']),
        ], is_array($config['setup_state'] ?? null) ? $config['setup_state'] : []);

        return $this->response->setJSON($config);
    }

    public function publish(): ResponseInterface
    {
        $payload = $this->jsonRequestPayload();

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $errors = $this->validatePublishPayload($payload);
        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'errors' => $errors]);
        }

        $result = $this->safeApiCall(fn () => $this->entryService->create($payload));

        $statusCode = $this->normalizeUpstreamStatus($result);

        $body = $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []);

        if ($statusCode >= 200 && $statusCode < 300 && ! isset($body['ok'])) {
            $body = ['ok' => true] + $body;
        } elseif ($statusCode >= 400) {
            $body = [
                'ok' => false,
                'message' => $result['messages'][0] ?? $result['message'] ?? lang('Wizard.error_publish'),
                'errors' => $result['fieldErrors'] ?? [],
                'data' => $body,
            ];
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($body);
    }

    public function uploadImage(): ResponseInterface
    {
        $file = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? $this->request->getFile('file')
            : null;

        if ($file === null || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'No valid file provided']);
        }

        $mimeError = $this->validateImageFile($file);
        if ($mimeError !== null) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $mimeError]);
        }

        $result = $this->safeApiCall(fn () => $this->fileService->upload(
            'file',
            $file->getTempName(),
            $file->getClientName(),
            $file->getMimeType()
        ));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []));
    }

    // ── WIZ-007: Edit page ────────────────────────────────────────────────────

    public function createBlock(int $pageId): ResponseInterface
    {
        return $this->handleCreateBlock('page', $pageId);
    }

    public function createEntryBlock(int $entryId): ResponseInterface
    {
        return $this->handleCreateBlock('entry', $entryId);
    }

    private function handleCreateBlock(string $ownerType, int $ownerId): ResponseInterface
    {
        $payload = $this->jsonRequestPayload();

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        if (empty($payload['block_id']) || (int) $payload['block_id'] <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'block_id is required']);
        }

        $result = $this->safeApiCall(fn () => $this->blockInstanceService->create($ownerId, $ownerType, $payload));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function deleteBlock(int $pageId, int $blockId): ResponseInterface
    {
        return $this->handleDeleteBlock('page', $pageId, $blockId);
    }

    public function deleteEntryBlock(int $entryId, int $blockId): ResponseInterface
    {
        return $this->handleDeleteBlock('entry', $entryId, $blockId);
    }

    private function handleDeleteBlock(string $ownerType, int $ownerId, int $blockId): ResponseInterface
    {
        $result = $this->safeApiCall(fn () => $this->blockInstanceService->delete($ownerId, $ownerType, $blockId));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response->setStatusCode($statusCode)->setJSON(['ok' => $statusCode < 300]);
    }

    public function pageBlocks(int $pageId): ResponseInterface
    {
        return $this->handleListBlocks('page', $pageId);
    }

    public function entryBlocks(int $entryId): ResponseInterface
    {
        return $this->handleListBlocks('entry', $entryId);
    }

    private function handleListBlocks(string $ownerType, int $ownerId): ResponseInterface
    {
        $result = $this->safeApiCall(
            fn () => $this->blockInstanceService->list($ownerId, $ownerType, ['include_translations' => 1, 'limit' => 100])
        );

        if (isset($result['ok']) && $result['ok'] === false) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'message' => 'Could not load blocks']);
        }

        return $this->response->setJSON($this->extractData($result));
    }

    public function updateBlock(int $pageId, int $blockId): ResponseInterface
    {
        return $this->handleUpdateBlock('page', $pageId, $blockId);
    }

    public function updateEntryBlock(int $entryId, int $blockId): ResponseInterface
    {
        return $this->handleUpdateBlock('entry', $entryId, $blockId);
    }

    private function handleUpdateBlock(string $ownerType, int $ownerId, int $blockId): ResponseInterface
    {
        $payload = $this->jsonRequestPayload();

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $result = $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $blockId, $payload));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    // ── WIZ-008: Edit menu ────────────────────────────────────────────────────

    public function menuItems(int $menuId): ResponseInterface
    {
        $result = $this->safeApiCall(fn () => $this->menuService->listItems([
            'menu_id' => $menuId, 'include_translations' => 1, 'limit' => 100, 'sort' => 'sort_order', 'direction' => 'asc',
        ]));

        if (isset($result['ok']) && $result['ok'] === false) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'message' => 'Could not load menu items']);
        }

        return $this->response->setJSON($this->extractData($result));
    }

    public function addMenuItem(int $menuId): ResponseInterface
    {
        $payload = $this->jsonRequestPayload();

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $payload['menu_id'] = $menuId;
        $result = $this->safeApiCall(fn () => $this->menuService->createItem($payload));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function updateMenuItem(int $itemId): ResponseInterface
    {
        $payload = $this->jsonRequestPayload();

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        // Fetch current item to merge translations and prevent validation failures
        $currentItemResult = $this->safeApiCall(fn () => $this->menuService->getItem($itemId));
        if (isset($currentItemResult['ok']) && $currentItemResult['ok']) {
            $currentItem = $currentItemResult['data'] ?? [];
            if (! isset($payload['translations']) && is_array($currentItem) && isset($currentItem['translations'])) {
                $payload['translations'] = $currentItem['translations'];
            }
        }

        $result = $this->safeApiCall(fn () => $this->menuService->updateItem($itemId, $payload));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function deleteMenuItem(int $itemId): ResponseInterface
    {
        $result = $this->safeApiCall(fn () => $this->menuService->deleteItem($itemId));

        $statusCode = $this->normalizeUpstreamStatus($result);

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    // ── Validation helpers ────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>  field → error message
     */
    private function validatePublishPayload(array $payload): array
    {
        $errors = [];

        $collectionId = $payload['collection_id'] ?? null;
        if ($collectionId === null || (int) $collectionId <= 0) {
            $errors['collection_id'] = 'collection_id is required and must be a positive integer';
        }

        $title = $payload['title'] ?? '';
        if (!is_string($title) || trim($title) === '') {
            $errors['title'] = 'title is required';
        }

        $status = $payload['status'] ?? '';
        if (!in_array($status, ['published', 'draft'], true)) {
            $errors['status'] = 'status must be "published" or "draft"';
        }

        return $errors;
    }

    /**
     * Validates that the uploaded file is an image within the allowed size limit.
     * Uses fileinfo (getMimeType) to verify the real MIME type, not just the
     * client-reported Content-Type.
     *
     * @param object $file UploadedFile
     */
    private function validateImageFile(object $file): ?string
    {
        if (!method_exists($file, 'getMimeType') || !method_exists($file, 'getSize')) {
            return null;
        }

        $realMime = strtolower((string) ($file->getMimeType() ?: ''));
        if (!str_starts_with($realMime, 'image/')) {
            return 'Only image files are allowed (received: ' . ($realMime ?: 'unknown') . ')';
        }

        $size = (int) $file->getSize();
        $maxBytes = FileSizeLimits::effectiveMaxBytes();
        if ($size > $maxBytes) {
            $maxMb = FileSizeLimits::bytesToMb($maxBytes);
            return "File size exceeds the {$maxMb} MB limit";
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\MenuApiService;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Cms\Support\PagePresetApplier;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class StructureWizardController extends BaseWebController
{
    protected CollectionApiService $collectionService;
    protected PageApiService $pageService;
    protected MenuApiService $menuService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->collectionService = service('collectionApiService');
        $this->pageService = service('pageApiService');
        $this->menuService = service('menuApiService');
    }

    public function index(): ResponseInterface|string
    {
        if (! (has_permission('cms.pages.write') || has_permission('cms.menus.write') || has_permission('cms.collections.write'))) {
            return redirect()->to(site_url('dashboard'))->with('error', lang('Auth.noPermission'));
        }

        $languages = $this->loadActiveLanguages();

        return $this->render('cms/wizard/structure', [
            'title'      => lang('Wizard.structure_title'),
            'csrfName'   => csrf_token(),
            'csrfToken'  => csrf_hash(),
            'languages'  => $languages,
        ]);
    }

    public function config(): ResponseInterface
    {
        if (! (has_permission('cms.pages.write') || has_permission('cms.menus.write') || has_permission('cms.collections.write'))) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $languages = $this->loadActiveLanguages();
        $activeBlockKeys = $this->activeBlockKeys();
        $collectionPresets = CmsPresetCatalog::filterAvailablePresets(CmsPresetCatalog::collectionPresets(), $activeBlockKeys);
        $pagePresets = CmsPresetCatalog::filterAvailablePresets(CmsPresetCatalog::pagePresets(), $activeBlockKeys);

        return $this->response->setJSON([
            'ok' => true,
            'data' => [
                'languages' => $languages,
                'collection_types' => CmsPresetCatalog::collectionTypeOptions(),
                'collection_presets' => array_column($collectionPresets, null, 'type_key'),
                'page_types' => CmsPresetCatalog::pageTypeOptions(),
                'page_presets' => array_column($pagePresets, null, 'type_key'),
                'setup_state' => [
                    'has_languages' => $languages !== [],
                    'has_active_block_types' => $activeBlockKeys !== [],
                    'has_collection_presets' => $collectionPresets !== [],
                    'has_page_presets' => $pagePresets !== [],
                ],
            ],
        ]);
    }

    public function createPage(): ResponseInterface
    {
        if (! has_permission('cms.pages.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $payload = $this->jsonRequestPayload();
        $result = $this->safeApiCall(fn () => $this->pageService->create($payload));
        $statusCode = $this->normalizeUpstreamStatus($result);
        if ($statusCode >= 200 && $statusCode < 300) {
            $data = $this->extractData($result);
            $pageId = (int) ($data['id'] ?? 0);
            if ($pageId > 0) {
                PagePresetApplier::fromServices()->apply($pageId, (string) ($payload['page_type'] ?? 'generic'), $payload);
            }
        }
        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []),
        ]);
    }

    public function createMenu(): ResponseInterface
    {
        if (! has_permission('cms.menus.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $payload = $this->jsonRequestPayload();
        $result = $this->safeApiCall(fn () => $this->menuService->create($payload));
        $statusCode = $this->normalizeUpstreamStatus($result);
        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []),
        ]);
    }

    public function createCollection(): ResponseInterface
    {
        if (! has_permission('cms.collections.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $payload = $this->jsonRequestPayload();

        $validationError = $this->validateCollectionWizardPayload($payload);
        if ($validationError !== null) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $validationError]);
        }

        $result = $this->safeApiCall(fn () => $this->collectionService->create($payload));
        $statusCode = $this->normalizeUpstreamStatus($result);

        $ok = $statusCode >= 200 && $statusCode < 300;
        $data = $ok ? $this->extractData($result) : ($result['data'] ?? []);
        $fieldErrors = $this->getFieldErrors($result);
        $message = null;

        if ($ok) {
            $createdId = (string) ($data['id'] ?? '');
            if ($createdId === '') {
                $ok = false;
                $statusCode = 502;
                $message = lang('Wizard.wizard_structure_error_collection_missing_id');
            }
        }

        if (! $ok) {
            $message = $message ?? $this->firstMessage($result, lang('Wizard.wizard_structure_error_collection'));

            if ($fieldErrors !== []) {
                $message = reset($fieldErrors) ?: $message;
            }
        }

        $response = [
            'ok' => $ok,
            'data' => $data,
        ];

        if (! $ok && $message !== null && $message !== '') {
            $response['message'] = $message;
        }

        if (! $ok && $fieldErrors !== []) {
            $response['fieldErrors'] = $fieldErrors;
        }

        if (! $ok && isset($result['detail']) && is_scalar($result['detail'])) {
            $response['detail'] = (string) $result['detail'];
        }

        if (! $ok && isset($result['errors']) && is_array($result['errors'])) {
            $response['errors'] = $result['errors'];
        }

        return $this->response->setStatusCode($statusCode)->setJSON($response);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateCollectionWizardPayload(array $payload): ?string
    {
        $collectionKey = trim((string) ($payload['collection_key'] ?? ''));
        $collectionType = trim((string) ($payload['collection_type'] ?? ''));

        if ($collectionKey === '' || strlen($collectionKey) < 2) {
            return 'collection_key is required.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $collectionKey)) {
            return 'collection_key must use lowercase letters, numbers and hyphens only.';
        }

        if ($collectionType === '' || ! preg_match('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', $collectionType)) {
            return 'collection_type is required.';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function activeBlockKeys(): array
    {
        $catalog = service('blockCatalogService')->all();
        $keys = [];
        foreach ($catalog as $blockType) {
            $key = (string) ($blockType['block_key'] ?? '');
            if ($key !== '') {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return list<array<mixed>>
     */
    private function loadActiveLanguages(): array
    {
        $languageService = service('languageApiService');
        $languagesResponse = $this->safeApiCall(fn () => $languageService->list(['limit' => 100, 'is_active' => true]));
        if (! $languagesResponse['ok']) {
            $this->maybeFlashDevError($languagesResponse);
        }

        $rows = [];
        foreach ($this->extractItems($languagesResponse) as $row) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

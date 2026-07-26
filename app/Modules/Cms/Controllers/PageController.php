<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\PageStoreRequest;
use App\Modules\Cms\Requests\PageUpdateRequest;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Cms\Support\PagePresetApplier;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PageController extends BaseWebController
{
    protected PageApiService $pageService;
    protected \App\Modules\Cms\Services\CollectionApiService $collectionService;
    protected TranslationAuditApiService $translationAuditService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->pageService = service('pageApiService');
        $this->collectionService = service('collectionApiService');
        $this->translationAuditService = service('translationAuditApiService');
    }

    public function index(): string
    {
        return $this->render('cms/pages/index', [
            'title'        => lang('Pages.pages_title'),
            'limitOptions' => [10, 25, 50, 100],
            'pages' => $this->pagesOptions(),
            'languages'    => $this->getLanguages(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['parent_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->pageService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->pageService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->render('cms/pages/show', [
                'title'      => lang('Pages.pages_details'),
                'page'       => [],
                'error'      => $this->firstMessage($response, lang('Pages.pages_not_found')),
                'pages'      => $this->pagesOptions(),
                'blocks'     => [],
                'blockTypes' => [],
                'languages'  => [],
                'blockTranslationStatus'  => [],
            ]);
        }

        $blocksResp = $this->safeApiCall(
            fn () => service('blockInstanceApiService')->list($id, 'page')
        );
        $allBlocks = $blocksResp['ok'] ? $this->extractItems($blocksResp) : [];
        $blocks    = array_values(
            array_filter($allBlocks, static fn (array $b) => empty($b['parent_instance_id']))
        );

        return $this->render('cms/pages/show', [
            'title'         => lang('Pages.pages_details'),
            'page'          => $this->extractData($response),
            'pages'         => $this->pagesOptions(),
            'collections'   => $this->collectionsOptions(),
            'publicSiteUrl' => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
            'blocks'        => $blocks,
            'blockTypes'    => $this->fetchBlockTypesIndexed(),
            'languages'     => $this->getLanguages(),
            'blockTranslationStatus'  => $this->ownerBlockTranslationStatus('page', $id),
        ]);
    }

    /**
     * Translation status for every top-level/child block of this page.
     * Degrades to empty on API failure so a status outage never breaks the
     * page detail page itself.
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
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        $this->maybeFlashDevError($response);

        return $this->extractItems($response);
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.pages.write')) {
            return redirect()->to(route_to('admin.cms.pages'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/pages/create', [
            'title' => lang('Pages.pages_create'),
            'pages' => $this->pagesOptions(),
            'languages' => $languages,
            'collections' => $this->collectionsOptions(),
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'pageTypes' => $this->pageTypeOptions(),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var PageStoreRequest $request */
        $request = service('formRequest', PageStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $payload = $request->payload();
        $response = $this->safeApiCall(fn () => $this->pageService->create($payload));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Pages.pages_create_failed'));
        }

        $pageId = (int) ($this->extractData($response)['id'] ?? 0);
        if ($pageId > 0) {
            $this->applyPagePreset($pageId, (string) ($payload['page_type'] ?? 'generic'), $payload);
        }

        return redirect()->to(route_to('admin.cms.pages'))->with('success', lang('Pages.pages_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->pageService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->withError(lang('Pages.pages_not_found'), route_to('admin.cms.pages'));
        }

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/pages/edit', [
            'title' => lang('Pages.pages_edit'),
            'item' => $this->extractData($response),
            'pages' => $this->pagesOptions($id),
            'languages' => $languages,
            'collections' => $this->collectionsOptions(),
            'focusLangId' => $focusLangId,
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'pageTypes' => $this->pageTypeOptions(),
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var PageUpdateRequest $request */
        $request = service('formRequest', PageUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->pageService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Pages.pages_update_failed'));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.pages.show', $id)))->with('success', lang('Pages.pages_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->pageService->delete($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->failApi($response, lang('Pages.pages_delete_failed'), route_to('admin.cms.pages'), false);
        }

        return redirect()->to(route_to('admin.cms.pages'))->with('success', lang('Pages.pages_delete_success'));
    }



    public function checkSlug(): ResponseInterface
    {
        $slugRaw       = $this->request->getGet('slug');
        $languageIdRaw = $this->request->getGet('language_id');
        $currentIdRaw  = $this->request->getGet('current_id');
        $slug          = is_scalar($slugRaw) ? (string) $slugRaw : '';
        $languageId    = is_scalar($languageIdRaw) ? (int)    $languageIdRaw : 0;
        $currentId     = is_scalar($currentIdRaw) ? (string) $currentIdRaw : '';

        if ($slug === '' || $languageId === 0) {
            return $this->response->setJSON(['available' => false]);
        }

        $result = $this->safeApiCall(fn () => $this->pageService->checkSlug($slug, $languageId, $currentId));
        $data   = $this->extractData($result);
        return $this->response->setJSON(['available' => (bool) ($data['available'] ?? false)]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function applyPagePreset(int $pageId, string $pageType, array $context = []): void
    {
        PagePresetApplier::fromServices()->apply($pageId, $pageType, $context);
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function pageTypeOptions(): array
    {
        return array_map(
            function (string $type): array {
                return [
                    'key' => $type,
                    'label' => lang('Pages.page_type_' . $type),
                ];
            },
            CmsPresetCatalog::pageTypes()
        );
    }

    /**
     * @return array<string, string>
     */
    private function collectionsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->list(['limit' => 200, 'is_active' => true]));
        $this->maybeFlashDevError($response);
        $items = $this->extractItems($response);
        $options = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = (string) ($item['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $options[$id] = (string) ($item['name'] ?? $item['collection_key'] ?? $id);
        }

        return $options;
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->pageService->list(['limit' => 250, 'sort' => 'sort_order']));
        $this->maybeFlashDevError($response);
        $items = $this->extractItems($response);

        return $this->render('cms/pages/reorder', [
            'title' => lang('Pages.pages_title') . ' - ' . lang('Pages.field_sort_order'),
            'items' => $items,
        ]);
    }

    public function saveOrder(): ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        $request = $this->request;
        if (! $request instanceof \CodeIgniter\HTTP\IncomingRequest) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid request type',
            ])->setStatusCode(400);
        }

        $json = $request->getJSON(true);
        $jsonArray = is_array($json) ? $json : [];
        $items = $jsonArray['items'] ?? [];

        if (! is_array($items)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid payload structure',
            ])->setStatusCode(400);
        }

        foreach ($items as $item) {
            $id = (string) ($item['id'] ?? '');
            $value = isset($item['sort_order']) ? (int) $item['sort_order'] : 0;

            if ($id !== '') {
                $this->pageService->update($id, ['sort_order' => $value]);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved.',
        ]);
    }


    public function publish(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->pageService->publish($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Pages.pages_publish_failed'), route_to('admin.cms.pages.show', $id), false);
        }

        return redirect()->to(route_to('admin.cms.pages.show', $id))->with('success', lang('Pages.pages_publish_success'));
    }

    public function archive(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->pageService->archive($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Pages.pages_archive_failed'), route_to('admin.cms.pages.show', $id), false);
        }

        return redirect()->to(route_to('admin.cms.pages.show', $id))->with('success', lang('Pages.pages_archive_success'));
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchBlockTypesIndexed(): array
    {
        $types = service('blockCatalogService')->indexed();

        $indexed = [];
        foreach ((array) $types as $t) {
            if (is_array($t) && isset($t['id'])) {
                $indexed[(int) $t['id']] = $t;
            }
        }
        return $indexed;
    }

    /** @return array<string, string> */
    private function pagesOptions(?string $excludeId = null): array
    {
        $response = $this->safeApiCall(fn () => $this->pageService->pages(['limit' => 250]));
        $this->maybeFlashDevError($response);
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            if ($excludeId !== null && (string)$item['id'] === (string)$excludeId) {
                continue;
            }
            $title = null;
            if (! empty($item['translations']) && is_array($item['translations'])) {
                foreach ($item['translations'] as $t) {
                    if (is_array($t) && ! empty($t['title'])) {
                        $title = $t['title'];
                        break;
                    }
                }
            }
            $label = $title ?? $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['email'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }
}

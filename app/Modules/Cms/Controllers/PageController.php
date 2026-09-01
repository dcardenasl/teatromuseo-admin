<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\PageStoreRequest;
use App\Modules\Cms\Requests\PageUpdateRequest;
use App\Modules\Cms\Services\CmsBootstrapBffAdapter;
use App\Modules\Cms\Services\CmsWorkspaceBffAdapter;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Cms\Support\PagePresetApplier;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PageController extends BaseWebController
{
    protected PageApiService $pageService;
    protected CmsBootstrapBffAdapter $cmsBootstrap;
    protected CmsWorkspaceBffAdapter $cmsWorkspace;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->pageService = service('pageApiService');
        $this->cmsBootstrap = service('cmsBootstrapBffAdapter');
        $this->cmsWorkspace = service('cmsWorkspaceBffAdapter');
    }

    public function index(): string
    {
        $bootstrap = $this->cmsBootstrap->pageFormOptions() ?? [];

        return $this->render('cms/pages/index', [
            'title'        => lang('Pages.pages_title'),
            'limitOptions' => [10, 25, 50, 100],
            'pages' => is_array($bootstrap['pages'] ?? null)
                ? $this->pageOptionsFromBootstrap($bootstrap['pages'])
                : [],
            'languages' => is_array($bootstrap['languages'] ?? null) ? $bootstrap['languages'] : [],
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['parent_id', 'status', 'page_type'],
            ['name', 'page_type', 'status', 'parent_id', 'is_in_sitemap', 'created_at'],
            fn (array $params) => $this->pageService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $workspace = $this->cmsWorkspace->page((int) $id);
        if ($workspace === null || ! is_array($workspace['page'] ?? null)) {
            return $this->render('cms/pages/show', [
                'title'      => lang('Pages.pages_details'),
                'page'       => [],
                'pages'      => [],
                'collections' => [],
                'publicSiteUrl' => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
                'blocks'     => [],
                'blockTypes' => [],
                'languages'  => [],
                'blockTranslationStatus' => [],
                'quality'    => [],
                'error'      => $this->cmsWorkspace->wasUnavailable()
                    ? lang('App.connection_error')
                    : lang('Pages.pages_not_found'),
            ]);
        }

        $page = is_array($workspace['page'] ?? null) ? $workspace['page'] : [];
        $allBlocks = is_array($workspace['blocks'] ?? null) ? $workspace['blocks'] : [];
        $blocks    = array_values(
            array_filter($allBlocks, static fn (array $b) => empty($b['parent_instance_id']))
        );

        return $this->render('cms/pages/show', [
            'title'         => lang('Pages.pages_details'),
            'page'          => $page,
            'pages'         => $this->pageOptionsFromBootstrap((array) ($workspace['pages'] ?? [])),
            'collections'   => $this->collectionOptionsFromBootstrap((array) ($workspace['collections'] ?? [])),
            'publicSiteUrl' => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
            'blocks'        => $blocks,
            'blockTypes'    => (array) ($workspace['blockTypes'] ?? []),
            'languages'     => (array) ($workspace['languages'] ?? []),
            'blockTranslationStatus'  => (array) ($workspace['blockTranslationStatus'] ?? []),
            'quality'       => (array) ($workspace['quality'] ?? []),
        ]);
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
        $bootstrap = $this->cmsBootstrap->pageFormOptions() ?? [];
        $languages = is_array($bootstrap['languages'] ?? null)
            ? $bootstrap['languages']
            : [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/pages/create', [
            'title' => lang('Pages.pages_create'),
            'pages' => is_array($bootstrap['pages'] ?? null)
                ? $this->pageOptionsFromBootstrap($bootstrap['pages'])
                : [],
            'languages' => $languages,
            'collections' => is_array($bootstrap['collections'] ?? null)
                ? $this->collectionOptionsFromBootstrap($bootstrap['collections'])
                : [],
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

        return redirect()->to(route_to('admin.cms.pages.show', (string) $pageId))->with('success', lang('Pages.pages_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $workspace = $this->cmsWorkspace->page((int) $id);
        if ($workspace !== null && is_array($workspace['page'] ?? null)) {
            $focusLangRaw = $this->request->getGet('focus_lang');
            $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
                ? (int) $focusLangRaw
                : 0;

            $bootstrap = $workspace;
            $languages = is_array($bootstrap['languages'] ?? null) ? $bootstrap['languages'] : [];
            $languageContext = $this->resolveLanguageContext($languages);
            $defaultLangId = $languageContext['defaultLangId'];
            $fieldMap = ['title', 'excerpt', 'meta_title', 'meta_description'];
            $translateTargets = ($defaultLangId > 0 && !empty($languages))
                ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
                : [];

            return $this->render('cms/pages/edit', [
                'title' => lang('Pages.pages_edit'),
                'item' => $workspace['page'],
                'pages' => is_array($bootstrap['pages'] ?? null)
                    ? $this->pageOptionsFromBootstrap($bootstrap['pages'], $id)
                    : [],
                'languages' => $languages,
                'collections' => is_array($bootstrap['collections'] ?? null)
                    ? $this->collectionOptionsFromBootstrap($bootstrap['collections'])
                    : [],
                'focusLangId' => $focusLangId,
                'defaultLangId' => $languageContext['defaultLangId'],
                'defaultLangCode' => $languageContext['defaultLangCode'],
                'defaultLangIndex' => $languageContext['defaultLangIndex'],
                'translateTargets' => $translateTargets,
                'pageTypes' => $this->pageTypeOptions(),
                'returnTo' => $this->incomingReturnTo(),
                'quality' => (array) ($workspace['quality'] ?? []),
            ]);
        }

        return $this->withError(
            $this->cmsWorkspace->wasUnavailable() ? lang('App.connection_error') : lang('Pages.pages_not_found'),
            route_to('admin.cms.pages'),
        );
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
     * @param array<int|string, mixed> $items
     * @return array<string, string>
     */
    private function collectionOptionsFromBootstrap(array $items): array
    {
        $options = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $options[(string) $item['id']] = (string) ($item['name'] ?? $item['collection_key'] ?? $item['id']);
        }

        return $options;
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<string, string>
     */
    private function pageOptionsFromBootstrap(array $items, ?string $excludeId = null): array
    {
        $options = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id']) || ($excludeId !== null && (string) $item['id'] === $excludeId)) {
                continue;
            }
            $title = null;
            $translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            foreach ($translations as $translation) {
                if (is_array($translation) && ! empty($translation['title'])) {
                    $title = (string) $translation['title'];
                    break;
                }
            }
            $options[(string) $item['id']] = $title ?? (string) ($item['name'] ?? $item['title'] ?? $item['id']);
        }

        return $options;
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->pageService->list([
            'limit' => 250,
            'sort' => 'sort_order',
            'projection' => 'list',
        ]));
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

        return $this->saveSortOrderFromJson(
            'cms',
            'pages',
            [],
            lang('Files.gallery_save_success') ?? 'Order saved.',
        );
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


}

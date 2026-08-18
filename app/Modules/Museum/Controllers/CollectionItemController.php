<?php

declare(strict_types=1);

namespace App\Modules\Museum\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Museum\Requests\CollectionItemStoreRequest;
use App\Modules\Museum\Requests\CollectionItemUpdateRequest;
use App\Modules\Museum\Services\CatalogCollectionItemBffAdapter;
use App\Modules\Museum\Services\CollectionItemApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CollectionItemController extends BaseWebController
{
    protected CollectionItemApiServiceInterface $collectionItemService;
    protected CatalogCollectionItemBffAdapter $workspaceAdapter;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->collectionItemService = service('museumCollectionItemApiService');
        $this->workspaceAdapter = service('catalogCollectionItemBffAdapter');
    }

    public function index(): string
    {
        return $this->render('museum/collection_items/index', [
            'title'        => lang('Museum.collection_items_title'),
            'limitOptions' => [10, 25, 50, 100],
            'categories'   => $this->categoriesOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['category_id'],
            ['name', 'created_at'],
            fn (array $params): array => $this->collectionItemService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $workspace = $this->workspaceAdapter->workspace((int) $id);
        if ($workspace !== null && is_array($workspace['collectionItem'] ?? null)) {
            return $this->render('museum/collection_items/show', [
                'title' => lang('Museum.collection_items_details'),
                'collectionItem' => $workspace['collectionItem'],
                'categories' => $this->workspaceOptions($workspace['categories'] ?? []),
                'languages' => is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [],
            ]);
        }
        return $this->render('museum/collection_items/show', [
            'title' => lang('Museum.collection_items_details'),
            'collectionItem' => [],
            'error' => $this->workspaceAdapter->wasUnavailable()
                ? lang('App.connection_error')
                : lang('Museum.collection_items_not_found'),
            'categories' => [],
            'languages' => [],
        ]);
    }

    public function create(): string
    {
        $workspace = $this->workspaceAdapter->workspace();
        $languages = $workspace !== null && is_array($workspace['languages'] ?? null)
            ? $workspace['languages']
            : [];
        $categories = $workspace !== null ? $this->workspaceOptions($workspace['categories'] ?? []) : [];
        $techniques = $workspace !== null ? $this->workspaceOptions($workspace['techniques'] ?? []) : [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && ! empty($languages))
            ? $this->buildTranslateTargets($languages, ['name', 'summary', 'curiosidad', 'contenido', 'physical_description', 'ubicacion'], $defaultLangId)
            : [];

        return $this->render('museum/collection_items/create', [
            'title'            => lang('Museum.collection_items_create'),
            'categories'       => $categories,
            'techniques'       => $techniques,
            'languages'        => $languages,
            'defaultLangId'    => $defaultLangId,
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var CollectionItemStoreRequest $request */
        $request = service('formRequest', CollectionItemStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->collectionItemService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.collection_items_create_failed'));
        }

        $this->invalidatePublicSiteCache('collection_items');

        return redirect()->to(route_to('admin.museum.collection_items'))->with('success', lang('Museum.collection_items_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $workspace = $this->workspaceAdapter->workspace((int) $id);
        if ($workspace !== null && is_array($workspace['collectionItem'] ?? null)) {
            $item = $workspace['collectionItem'];
            $languages = is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [];
            $languageContext = $this->resolveLanguageContext($languages);
            $defaultLangId = $languageContext['defaultLangId'];
            $translateTargets = ($defaultLangId > 0 && $languages !== [])
                ? $this->buildTranslateTargets($languages, ['name', 'summary', 'curiosidad', 'contenido', 'physical_description', 'ubicacion'], $defaultLangId)
                : [];

            return $this->render('museum/collection_items/edit', [
                'title' => lang('Museum.collection_items_edit'),
                'item' => $item,
                'categories' => $this->workspaceOptions($workspace['categories'] ?? []),
                'techniques' => $this->workspaceOptions($workspace['techniques'] ?? []),
                'languages' => $languages,
                'defaultLangId' => $defaultLangId,
                'defaultLangIndex' => $languageContext['defaultLangIndex'],
                'defaultLangCode' => $languageContext['defaultLangCode'],
                'translateTargets' => $translateTargets,
            ]);
        }
        return $this->withError(
            $this->workspaceAdapter->wasUnavailable()
                ? lang('App.connection_error')
                : lang('Museum.collection_items_not_found'),
            route_to('admin.museum.collection_items'),
        );
    }

    public function update(string $id): RedirectResponse
    {
        /** @var CollectionItemUpdateRequest $request */
        $request = service('formRequest', CollectionItemUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->collectionItemService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.collection_items_update_failed'));
        }

        $this->invalidatePublicSiteCache('collection_items');

        return redirect()->to(route_to('admin.museum.collection_items'))->with('success', lang('Museum.collection_items_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionItemService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.collection_items_delete_failed'), route_to('admin.museum.collection_items'), false);
        }

        $this->invalidatePublicSiteCache('collection_items');

        return redirect()->to(route_to('admin.museum.collection_items'))->with('success', lang('Museum.collection_items_delete_success'));
    }

    /** @return array<string, string> */
    private function categoriesOptions(): array
    {
        $categoryService = service('museumCategoryApiService');
        $response = $this->safeApiCall(fn () => $categoryService->list([
            'per_page' => 100,
            'projection' => 'list',
        ]));
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['email'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /**
     * @param mixed $items
     * @return array<string,string>
     */
    private function workspaceOptions(mixed $items): array
    {
        $options = [];
        if (! is_array($items)) {
            return $options;
        }
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $options[(string) $item['id']] = (string) ($item['name'] ?? $item['title'] ?? $item['label'] ?? $item['slug'] ?? $item['id']);
        }

        return $options;
    }
}

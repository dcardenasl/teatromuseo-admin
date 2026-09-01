<?php

declare(strict_types=1);

namespace App\Modules\Museum\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Museum\Requests\CategoryStoreRequest;
use App\Modules\Museum\Requests\CategoryUpdateRequest;
use App\Modules\Museum\Services\CategoryApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CategoryController extends BaseWebController
{
    protected CategoryApiServiceInterface $categoryService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->categoryService = service('museumCategoryApiService');
    }

    public function index(): string
    {
        return $this->render('museum/categories/index', [
            'title'        => lang('Museum.categories_title'),
            'limitOptions' => [10, 25, 50, 100],
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->categoryService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->get($id));

        if (! $response['ok']) {
            return $this->render('museum/categories/show', [
                'title'     => lang('Museum.categories_details'),
                'category'  => [],
                'error'     => $this->firstMessage($response, lang('Museum.categories_not_found')),
                'languages' => $this->getLanguages(),
            ]);
        }

        return $this->render('museum/categories/show', [
            'title'     => lang('Museum.categories_details'),
            'category'  => $this->extractData($response),
            'languages' => $this->getLanguages(),
        ]);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && ! empty($languages))
            ? $this->buildTranslateTargets($languages, ['name', 'short_description'], $defaultLangId)
            : [];

        return $this->render('museum/categories/create', [
            'title'            => lang('Museum.categories_create'),
            'languages'        => $languages,
            'defaultLangId'    => $defaultLangId,
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var CategoryStoreRequest $request */
        $request = service('formRequest', CategoryStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->categoryService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.categories_create_failed'));
        }

        $this->invalidatePublicSiteCache('categories');

        return redirect()->to(route_to('admin.museum.categories'))->with('success', lang('Museum.categories_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Museum.categories_not_found'), route_to('admin.museum.categories'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && ! empty($languages))
            ? $this->buildTranslateTargets($languages, ['name', 'short_description'], $defaultLangId)
            : [];

        return $this->render('museum/categories/edit', [
            'title'            => lang('Museum.categories_edit'),
            'item'             => $this->extractData($response),
            'languages'        => $languages,
            'defaultLangId'    => $defaultLangId,
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var CategoryUpdateRequest $request */
        $request = service('formRequest', CategoryUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->categoryService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.categories_update_failed'));
        }

        $this->invalidatePublicSiteCache('categories');

        return redirect()->to(route_to('admin.museum.categories'))->with('success', lang('Museum.categories_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.categories_delete_failed'), route_to('admin.museum.categories'), false);
        }

        $this->invalidatePublicSiteCache('categories');

        return redirect()->to(route_to('admin.museum.categories'))->with('success', lang('Museum.categories_delete_success'));
    }

    public function reorder(): string
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->list(['limit' => 250, 'sort' => 'sort_order']));
        $items = $this->extractItems($response);

        return $this->render('museum/categories/reorder', [
            'title' => lang('Museum.categories_title') . ' - ' . lang('Museum.field_sort_order'),
            'items' => $items,
        ]);
    }

    public function saveOrder(): ResponseInterface
    {
        return $this->saveSortOrderFromJson(
            'catalog',
            'categories',
            [],
            lang('Museum.sort_order_saved'),
        );
    }

    /** @return array<string, mixed> */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));

        return $response['ok'] ? $this->extractItems($response) : [];
    }

}

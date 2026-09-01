<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\CategoryStoreRequest;
use App\Modules\Cms\Requests\CategoryUpdateRequest;
use App\Modules\Cms\Services\CategoryApiService;
use App\Modules\Cms\Services\CmsCategoryBffAdapter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CategoryController extends BaseWebController
{
    protected CategoryApiService $categoryService;
    protected CmsCategoryBffAdapter $categoryBootstrap;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->categoryService = service('categoryApiService');
        $this->categoryBootstrap = service('cmsCategoryBffAdapter');
    }

    public function index(): string
    {
        $sections = $this->categoryBootstrap->bootstrap() ?? [];

        return $this->render('cms/categories/index', [
            'title'        => lang('Categories.categories_title'),
            'limitOptions' => [10, 25, 50, 100],
            'collections'  => $this->optionMap($sections['collections'] ?? []),
            'categories'   => $this->optionMap($sections['categories'] ?? []),
            'languages'    => is_array($sections['languages'] ?? null) ? $sections['languages'] : [],
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['collection_id', 'parent_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->categoryService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $sections = $this->categoryBootstrap->bootstrap((int) $id) ?? [];
        $category = is_array($sections['category'] ?? null) ? $sections['category'] : [];
        $collections = $this->optionMap($sections['collections'] ?? []);
        $categories = $this->optionMap($sections['categories'] ?? []);
        $languages = is_array($sections['languages'] ?? null) ? $sections['languages'] : [];

        if ($category === []) {
            return $this->render('cms/categories/show', [
                'title' => lang('Categories.categories_details'),
                'category' => [],
                'error' => $this->categoryBootstrap->wasUnavailable()
                    ? lang('App.connection_error')
                    : lang('Categories.categories_not_found'),
                'collections' => $collections,
                'categories' => $categories,
                'languages' => $languages,
            ]);
        }

        return $this->render('cms/categories/show', [
            'title' => lang('Categories.categories_details'),
            'category' => $category,
            'collections' => $collections,
            'categories' => $categories,
            'languages' => $languages,
        ]);
    }

    public function create(): string
    {
        $sections = $this->categoryBootstrap->bootstrap() ?? [];
        $languages = is_array($sections['languages'] ?? null) ? $sections['languages'] : [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/categories/create', [
            'title'            => lang('Categories.categories_create'),
            'collections'      => $this->optionMap($sections['collections'] ?? []),
            'categories'       => $this->optionMap($sections['categories'] ?? []),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
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
            return $this->failApi($response, lang('Categories.categories_create_failed'));
        }

        $this->invalidatePublicSiteCache('categories');

        return redirect()->to(route_to('admin.cms.categories'))->with('success', lang('Categories.categories_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $sections = $this->categoryBootstrap->bootstrap((int) $id) ?? [];
        $item = is_array($sections['category'] ?? null) ? $sections['category'] : [];
        if ($item === []) {
            return $this->withError(lang('Categories.categories_not_found'), route_to('admin.cms.categories'));
        }

        $languages = is_array($sections['languages'] ?? null) ? $sections['languages'] : [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/categories/edit', [
            'title'            => lang('Categories.categories_edit'),
            'item'             => $item,
            'collections'      => $this->optionMap($sections['collections'] ?? []),
            'categories'       => $this->optionMap($sections['categories'] ?? [], $id),
            'languages'        => $languages,
            'focusLangId'      => $focusLangId,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'returnTo' => $this->incomingReturnTo(),
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
            return $this->failApi($response, lang('Categories.categories_update_failed'));
        }

        $this->invalidatePublicSiteCache('categories');

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.categories')))->with('success', lang('Categories.categories_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Categories.categories_delete_failed'), route_to('admin.cms.categories'), false);
        }

        $this->invalidatePublicSiteCache('categories');

        return redirect()->to(route_to('admin.cms.categories'))->with('success', lang('Categories.categories_delete_success'));
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

        $result = $this->safeApiCall(fn () => $this->categoryService->checkSlug($slug, $languageId, $currentId));
        $data   = $this->extractData($result);
        return $this->response->setJSON(['available' => (bool) ($data['available'] ?? false)]);
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->categoryService->list(['limit' => 250, 'sort' => 'sort_order']));
        $this->maybeFlashDevError($response);
        $items = $this->extractItems($response);

        return $this->render('cms/categories/reorder', [
            'title' => lang('Categories.categories_title') . ' - ' . lang('Categories.field_sort_order'),
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
            'categories',
            [],
            lang('Files.gallery_save_success') ?? 'Order saved.',
        );
    }



    /**
     * @param mixed $items
     * @return array<string, string>
     */
    private function optionMap(mixed $items, ?string $excludeId = null): array
    {
        $options = [];
        if (! is_array($items)) {
            return $options;
        }
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            if ($excludeId !== null && (string) $item['id'] === $excludeId) {
                continue;
            }
            $label = $item['name'] ?? $item['collection_key'] ?? $item['title'] ?? $item['label'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.categories.write')) {
            return redirect()->to(route_to('admin.cms.categories'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

}

<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\CategoryStoreRequest;
use App\Modules\Cms\Requests\CategoryUpdateRequest;
use App\Modules\Cms\Services\CategoryApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CategoryController extends BaseWebController
{
    protected CategoryApiService $categoryService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->categoryService = service('categoryApiService');
    }

    public function index(): string
    {
        return $this->render('cms/categories/index', [
            'title'        => lang('Categories.categories_title'),
            'limitOptions' => [10, 25, 50, 100],
            'collections' => $this->collectionsOptions(),
            'categories' => $this->categoriesOptions(),
            'languages'    => $this->getLanguages(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['collection_id', 'parent_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->categoryService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->render('cms/categories/show', [
                'title' => lang('Categories.categories_details'),
                'category' => [],
                'error' => $this->firstMessage($response, lang('Categories.categories_not_found')),
            'collections' => $this->collectionsOptions(),
            'categories' => $this->categoriesOptions(),
            ]);
        }

        return $this->render('cms/categories/show', [
            'title' => lang('Categories.categories_details'),
            'category' => $this->extractData($response),
            'collections' => $this->collectionsOptions(),
            'categories' => $this->categoriesOptions(),
            'languages' => $this->getLanguages(),
        ]);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/categories/create', [
            'title'            => lang('Categories.categories_create'),
            'collections'      => $this->collectionsOptions(),
            'categories'       => $this->categoriesOptions(),
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

        return redirect()->to(route_to('admin.cms.categories'))->with('success', lang('Categories.categories_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->withError(lang('Categories.categories_not_found'), route_to('admin.cms.categories'));
        }

        $languages = $this->getLanguages();
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
            'item'             => $this->extractData($response),
            'collections'      => $this->collectionsOptions(),
            'categories'       => $this->categoriesOptions($id),
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

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.categories')))->with('success', lang('Categories.categories_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Categories.categories_delete_failed'), route_to('admin.cms.categories'), false);
        }

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
                $this->categoryService->update($id, ['sort_order' => $value]);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved.',
        ]);
    }



    /** @return array<string, string> */
    private function collectionsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->collections(['limit' => 100, 'is_active' => true]));
        $this->maybeFlashDevError($response);
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = $item['collection_key'] ?? $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /** @return array<string, string> */
    private function categoriesOptions(?string $excludeId = null): array
    {
        $response = $this->safeApiCall(fn () => $this->categoryService->categories(['limit' => 100]));
        $this->maybeFlashDevError($response);
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            if ($excludeId !== null && (string) $item['id'] === $excludeId) {
                continue;
            }
            $label = $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['id'];
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

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        $this->maybeFlashDevError($response);
        return $this->extractItems($response);
    }
}

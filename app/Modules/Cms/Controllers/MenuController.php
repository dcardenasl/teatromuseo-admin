<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\MenuItemStoreRequest;
use App\Modules\Cms\Requests\MenuItemUpdateRequest;
use App\Modules\Cms\Requests\MenuStoreRequest;
use App\Modules\Cms\Requests\MenuUpdateRequest;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\MenuApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class MenuController extends BaseWebController
{
    protected MenuApiService $menuService;
    protected EntryApiService $entryService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->menuService = service('menuApiService');
        $this->entryService = service('entryApiService');
    }

    public function index(): string
    {
        return $this->render('cms/menus/index', [
            'title'        => lang('Menus.menus_title'),
            'limitOptions' => [10, 25, 50, 100],
            'languages'    => $this->getLanguages(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['menu_key', 'created_at'],
            function (array $params) {
                $response = $this->menuService->list([...$params, 'include_translations' => 1]);
                $menusPayload = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];
                $menus = $this->extractItems($response);

                if ($menus !== []) {
                    // Fetch all items to count them
                    $itemsResponse = $this->menuService->listItems([
                        'page' => 1,
                        'per_page' => 100,
                        'sort' => 'sort_order',
                    ]);
                    $items = $this->extractItems($itemsResponse);

                    // Group/count items by menu_id
                    $counts = [];
                    foreach ($items as $item) {
                        $mId = $item['menu_id'] ?? null;
                        if ($mId !== null) {
                            $counts[$mId] = ($counts[$mId] ?? 0) + 1;
                        }
                    }

                    // Inject count into each menu item
                    foreach ($menus as &$menu) {
                        $menu['items_count'] = $counts[$menu['id']] ?? 0;
                    }
                    unset($menu);

                    if (isset($menusPayload['data']) && is_array($menusPayload['data'])) {
                        $menusPayload['data'] = $menus;
                        $response['data'] = $menusPayload;
                    } else {
                        $response['data'] = $menus;
                    }

                    // Force the modified payload to be serialized instead of returning the
                    // original raw API body, which would skip our injected counts.
                    $response['raw'] = '';
                }
                return $response;
            }
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->menuService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/menus/show', [
                'title' => lang('Menus.menus_details'),
                'menu' => [],
                'error' => $this->firstMessage($response, lang('Menus.menus_not_found')),
            ]);
        }

        $itemsResponse = $this->menuService->listItems(['menu_id' => $id, 'limit' => 1000, 'sort' => 'sort_order']);
        $items = $this->extractItems($itemsResponse);

        return $this->render('cms/menus/show', [
            'title' => lang('Menus.menus_details'),
            'menu' => $this->extractData($response),
            'items' => $items,
            'languages' => $this->getLanguages(),
            'pages' => $this->pagesOptions(),
            'entries' => $this->entriesOptions(),
            'collections' => $this->collectionsOptions(),
        ]);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];

        return $this->render('cms/menus/create', [
            'title' => lang('Menus.menus_create'),
            'languages' => $languages,
            'defaultLangId' => $defaultLangId,
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'translateTargets' => $defaultLangId > 0
                ? $this->buildTranslateTargets($languages, ['name'], $defaultLangId)
                : [],
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var MenuStoreRequest $request */
        $request = service('formRequest', MenuStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->menuService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Menus.menus_create_failed'));
        }

        $createdMenu = $this->extractData($response);
        $newId = (string) ($createdMenu['id'] ?? '');
        $redirectTo = $newId !== '' ? route_to('admin.cms.menus.show', $newId) : route_to('admin.cms.menus');

        return redirect()->to($redirectTo)->with('success', lang('Menus.menus_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->menuService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Menus.menus_not_found'), route_to('admin.cms.menus'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/menus/edit', [
            'title' => lang('Menus.menus_edit'),
            'item'  => $this->extractData($response),
            'languages' => $languages,
            'focusLangId' => $focusLangId,
            'defaultLangId' => $defaultLangId,
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'translateTargets' => $defaultLangId > 0
                ? $this->buildTranslateTargets($languages, ['name'], $defaultLangId)
                : [],
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var MenuUpdateRequest $request */
        $request = service('formRequest', MenuUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->menuService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Menus.menus_update_failed'));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.menus.show', $id)))->with('success', lang('Menus.menus_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->menuService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Menus.menus_delete_failed'), route_to('admin.cms.menus'), false);
        }

        return redirect()->to(route_to('admin.cms.menus'))->with('success', lang('Menus.menus_delete_success'));
    }

    // MenuItem operations
    public function createItem(string $menuId): string
    {
        $menuResponse = $this->safeApiCall(fn () => $this->menuService->get($menuId));

        $itemsResponse = $this->menuService->listItems(['menu_id' => $menuId, 'limit' => 1000]);
        $items = $this->extractItems($itemsResponse);

        $languages = $this->getLanguages();
        $defaultLangId = $this->resolveLanguageContext($languages)['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, ['label', 'custom_url'], $defaultLangId, 'translations', true)
            : [];

        return $this->render('cms/menus/items/create', [
            'title'     => lang('Menus.menus_items_create') ?? 'Add Menu Item',
            'menuId'    => $menuId,
            'menu'      => $this->extractData($menuResponse),
            'items'     => $items,
            'pages'     => $this->pagesOptions(),
            'entries'   => $this->entriesOptions(),
            'collections' => $this->collectionsOptions(),
            'languages' => $languages,
            'translateTargets' => $translateTargets,
        ]);
    }

    public function storeItem(string $menuId): RedirectResponse
    {
        /** @var MenuItemStoreRequest $request */
        $request = service('formRequest', MenuItemStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $payload = $request->payload();

        if ($invalid = $this->validateMenuItemTarget($payload)) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->menuService->createItem($payload));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Menus.menus_items_create_failed') ?? 'Failed to create menu item.', route_to('admin.cms.menus.show', $menuId));
        }

        return redirect()->to(route_to('admin.cms.menus.show', $menuId))->with('success', lang('Menus.menus_items_create_success') ?? 'Menu item created successfully.');
    }

    public function editItem(string $menuId, string $itemId): string|RedirectResponse
    {
        $menuResponse = $this->safeApiCall(fn () => $this->menuService->get($menuId));
        $itemResponse = $this->safeApiCall(fn () => $this->menuService->getItem($itemId));
        if (! $itemResponse['ok']) {
            return $this->withError(lang('Menus.menus_items_not_found') ?? 'Menu item not found.', route_to('admin.cms.menus.show', $menuId));
        }

        $itemsResponse = $this->menuService->listItems(['menu_id' => $menuId, 'limit' => 1000]);
        $items = $this->extractItems($itemsResponse);

        $languages = $this->getLanguages();
        $defaultLangId = $this->resolveLanguageContext($languages)['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, ['label', 'custom_url'], $defaultLangId, 'translations', true)
            : [];

        return $this->render('cms/menus/items/edit', [
            'title'     => lang('Menus.menus_items_edit') ?? 'Edit Menu Item',
            'menuId'    => $menuId,
            'itemId'    => $itemId,
            'menu'      => $this->extractData($menuResponse),
            'item'      => $this->extractData($itemResponse),
            'items'     => $items,
            'pages'     => $this->pagesOptions(),
            'entries'   => $this->entriesOptions(),
            'collections' => $this->collectionsOptions(),
            'languages' => $languages,
            'translateTargets' => $translateTargets,
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function updateItem(string $menuId, string $itemId): RedirectResponse
    {
        /** @var MenuItemUpdateRequest $request */
        $request = service('formRequest', MenuItemUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $payload = $request->payload();

        if ($invalid = $this->validateMenuItemTarget($payload)) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->menuService->updateItem($itemId, $payload));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Menus.menus_items_update_failed') ?? 'Failed to update menu item.', route_to('admin.cms.menus.show', $menuId));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.menus.show', $menuId)))->with('success', lang('Menus.menus_items_update_success') ?? 'Menu item updated successfully.');
    }

    public function deleteItem(string $menuId, string $itemId): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->menuService->deleteItem($itemId));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Menus.menus_items_delete_failed') ?? 'Failed to delete menu item.', route_to('admin.cms.menus.show', $menuId), false);
        }

        return redirect()->to(route_to('admin.cms.menus.show', $menuId))->with('success', lang('Menus.menus_items_delete_success') ?? 'Menu item deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
        }
        return $this->extractItems($response);
    }

    /** @return array<string, string> */
    private function pagesOptions(?string $excludeId = null): array
    {
        $response = $this->safeApiCall(fn () => service('pageApiService')->pages(['limit' => 250]));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
        }
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

    /** @return array<string, string> */
    private function entriesOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->entryService->list(['limit' => 250]));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
        }
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            $label = null;
            if (! empty($item['translations']) && is_array($item['translations'])) {
                foreach ($item['translations'] as $translation) {
                    if (is_array($translation) && ! empty($translation['title'])) {
                        $label = $translation['title'];
                        break;
                    }
                }
            }

            $label ??= $item['title'] ?? $item['name'] ?? $item['slug'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /** @return array<string, string> */
    private function collectionsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->entryService->collections(['limit' => 100, 'is_active' => true]));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
        }
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

    /**
     * @param array<string, mixed> $payload
     */
    private function validateMenuItemTarget(array $payload): ?RedirectResponse
    {
        return match ($payload['link_type'] ?? '') {
            'page' => empty($payload['page_id'])
                ? redirect()->back()->withInput()->with('error', lang('Menus.field_page_id_required') ?? 'Page selection is required for Page link type.')
                : null,
            'entry' => empty($payload['entry_id'])
                ? redirect()->back()->withInput()->with('error', lang('Menus.field_entry_id_required') ?? 'An entry is required for Entry link type.')
                : null,
            'collection_listing' => empty($payload['collection_id'])
                ? redirect()->back()->withInput()->with('error', lang('Menus.field_collection_id_required') ?? 'A collection is required for Collection link type.')
                : null,
            'custom_url' => $this->validateCustomUrlTranslations($payload['translations'] ?? []),
            default => null,
        };
    }

    /**
     * @param array<int, mixed> $translations
     */
    private function validateCustomUrlTranslations(array $translations): ?RedirectResponse
    {
        foreach ($translations as $translation) {
            if (is_array($translation) && ! empty($translation['custom_url'])) {
                return null;
            }
        }

        return redirect()->back()->withInput()->with('error', lang('Menus.field_custom_url_required') ?? 'Custom URL is required for Custom URL link type.');
    }

    public function reorderItems(string $menuId): string|RedirectResponse
    {
        $menuResponse = $this->safeApiCall(fn () => $this->menuService->get($menuId));
        if (! $menuResponse['ok']) {
            return redirect()->to(route_to('admin.cms.menus'))->with('error', lang('Menus.menus_not_found') ?? 'Menu not found.');
        }

        $itemsResponse = $this->menuService->listItems(['menu_id' => $menuId, 'limit' => 1000]);
        $items = $this->extractItems($itemsResponse);

        // Sort items by sort_order initially
        usort($items, static function (array $a, array $b): int {
            return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
        });

        return $this->render('cms/menus/items/reorder', [
            'title'     => (lang('Menus.menus_items_title') ?? 'Menu Items') . ' - ' . (lang('App.reorder') ?? 'Reorder'),
            'menuId'    => $menuId,
            'menu'      => $this->extractData($menuResponse),
            'items'     => $items,
        ]);
    }

    public function saveItemsOrder(string $menuId): ResponseInterface
    {
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

        $itemsResponse = $this->menuService->listItems(['menu_id' => $menuId, 'limit' => 1000]);
        $existingItems = $this->extractItems($itemsResponse);
        $itemsById = [];
        foreach ($existingItems as $existingItem) {
            $itemsById[(string) ($existingItem['id'] ?? '')] = $existingItem;
        }

        foreach ($items as $item) {
            $id = (string) ($item['id'] ?? '');
            $value = isset($item['sort_order']) ? (int) $item['sort_order'] : 0;

            if ($id !== '' && isset($itemsById[$id])) {
                $payload = [
                    'sort_order'   => $value,
                    'translations' => $itemsById[$id]['translations'] ?? [],
                ];
                // Call updateItem directly with partial payload and validate response
                $response = $this->menuService->updateItem($id, $payload);
                if (! isset($response['ok']) || ! $response['ok']) {
                    return $this->response->setJSON([
                        'ok' => false,
                        'message' => $response['messages'][0] ?? $response['message'] ?? 'Error al guardar el orden del elemento #' . $id,
                    ])->setStatusCode(400);
                }
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved successfully.',
        ]);
    }

    public function getCategoryUrlOptions(): ResponseInterface
    {
        /** @var \App\Modules\Cms\Services\CategoryApiService $categoryService */
        $categoryService = service('categoryApiService');

        $collectionsResponse = $this->safeApiCall(fn () => $this->entryService->collections(['limit' => 100, 'is_active' => true]));
        $collections = $this->extractItems($collectionsResponse);

        $result = [];

        foreach ($collections as $collection) {
            if (! is_array($collection) || ! isset($collection['id'])) {
                continue;
            }

            $collectionId = (int) $collection['id'];
            $collectionKey = (string) ($collection['collection_key'] ?? '');

            // Fetch categories for this collection
            $categoriesResponse = $this->safeApiCall(fn () => $categoryService->list(['collection_id' => $collectionId, 'limit' => 500]));
            $categories = $this->extractItems($categoriesResponse);

            $categoryOptions = [];
            foreach ($categories as $cat) {
                if (! is_array($cat) || ! isset($cat['id'])) {
                    continue;
                }

                $catTranslations = [];
                if (! empty($cat['translations']) && is_array($cat['translations'])) {
                    foreach ($cat['translations'] as $trans) {
                        $langId = (int) ($trans['language_id'] ?? 0);
                        $catTranslations[$langId] = [
                            'slug' => (string) ($trans['slug'] ?? ''),
                            'name' => (string) ($trans['name'] ?? ''),
                        ];
                    }
                }

                $categoryOptions[] = [
                    'id' => $cat['id'],
                    'translations' => $catTranslations,
                ];
            }

            $collectionTranslations = [];
            if (! empty($collection['translations']) && is_array($collection['translations'])) {
                foreach ($collection['translations'] as $trans) {
                    $langId = (int) ($trans['language_id'] ?? 0);
                    $collectionTranslations[$langId] = [
                        'slug' => (string) ($trans['slug'] ?? ''),
                        'name' => (string) ($trans['name'] ?? ''),
                    ];
                }
            }

            $result[] = [
                'id' => $collectionId,
                'key' => $collectionKey,
                'name' => (string) ($collection['name'] ?? $collectionKey),
                'translations' => $collectionTranslations,
                'categories' => $categoryOptions,
            ];
        }

        return $this->response->setJSON([
            'ok' => true,
            'data' => $result,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\MenuItemStoreRequest;
use App\Modules\Cms\Requests\MenuItemUpdateRequest;
use App\Modules\Cms\Requests\MenuStoreRequest;
use App\Modules\Cms\Requests\MenuUpdateRequest;
use App\Modules\Cms\Services\CmsBootstrapBffAdapter;
use App\Modules\Cms\Services\MenuApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class MenuController extends BaseWebController
{
    protected MenuApiService $menuService;
    protected CmsBootstrapBffAdapter $cmsBootstrap;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->menuService = service('menuApiService');
        $this->cmsBootstrap = service('cmsBootstrapBffAdapter');
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
            fn (array $params) => $this->menuService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $bootstrap = $this->cmsBootstrap->menuEditorBootstrap((int) $id);
        if ($bootstrap === null) {
            return $this->render('cms/menus/show', [
                'title' => lang('Menus.menus_details'),
                'menu' => [],
                'items' => [],
                'languages' => [],
                'pages' => [],
                'entries' => [],
                'collections' => [],
                'error' => lang('App.connection_error'),
            ]);
        }

        $menu = is_array($bootstrap['menu'] ?? null) ? $bootstrap['menu'] : [];
        if ($menu === []) {
            return $this->render('cms/menus/show', [
                'title' => lang('Menus.menus_details'),
                'menu' => [],
                'items' => [],
                'languages' => [],
                'pages' => [],
                'entries' => [],
                'collections' => [],
                'error' => lang('Menus.menus_not_found'),
            ]);
        }

        $languages = is_array($bootstrap['languages'] ?? null) ? $bootstrap['languages'] : [];
        $items = is_array($bootstrap['items'] ?? null) ? $bootstrap['items'] : [];

        return $this->render('cms/menus/show', [
            'title' => lang('Menus.menus_details'),
            'menu' => $menu,
            'items' => $items,
            'languages' => $languages,
            'pages' => is_array($bootstrap['pages'] ?? null) ? $this->pagesOptionsFromItems($bootstrap['pages']) : [],
            'entries' => is_array($bootstrap['entries'] ?? null) ? $this->entriesOptionsFromItems($bootstrap['entries']) : [],
            'collections' => is_array($bootstrap['collections'] ?? null) ? $this->collectionsOptionsFromItems($bootstrap['collections']) : [],
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
        $bootstrap = $this->cmsBootstrap->menuEditorBootstrap((int) $menuId);
        if ($bootstrap === null) {
            return $this->render('cms/menus/items/create', [
                'title'            => lang('Menus.menus_items_create') ?? 'Add Menu Item',
                'menuId'           => $menuId,
                'menu'             => [],
                'items'            => [],
                'pages'            => [],
                'entries'          => [],
                'collections'      => [],
                'languages'        => [],
                'translateTargets' => [],
                'error'            => lang('App.connection_error'),
            ]);
        }

        $items = is_array($bootstrap['items'] ?? null)
            ? $bootstrap['items']
            : [];
        $languages = is_array($bootstrap['languages'] ?? null)
            ? $bootstrap['languages']
            : [];
        $defaultLangId = $this->resolveLanguageContext($languages)['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, ['label', 'custom_url'], $defaultLangId, 'translations', true)
            : [];

        return $this->render('cms/menus/items/create', [
            'title'     => lang('Menus.menus_items_create') ?? 'Add Menu Item',
            'menuId'    => $menuId,
            'menu'      => is_array($bootstrap['menu'] ?? null) ? $bootstrap['menu'] : [],
            'items'     => $items,
            'pages'     => is_array($bootstrap['pages'] ?? null) ? $this->pagesOptionsFromItems($bootstrap['pages']) : [],
            'entries'   => is_array($bootstrap['entries'] ?? null) ? $this->entriesOptionsFromItems($bootstrap['entries']) : [],
            'collections' => is_array($bootstrap['collections'] ?? null) ? $this->collectionsOptionsFromItems($bootstrap['collections']) : [],
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
        $bootstrap = $this->cmsBootstrap->menuEditorBootstrap((int) $menuId, (int) $itemId);
        if ($bootstrap === null) {
            return $this->withError(lang('App.connection_error'), route_to('admin.cms.menus.show', $menuId));
        }
        if (! is_array($bootstrap['item'] ?? null) || $bootstrap['item'] === []) {
            return $this->withError(lang('Menus.menus_items_not_found') ?? 'Menu item not found.', route_to('admin.cms.menus.show', $menuId));
        }

        $items = is_array($bootstrap['items'] ?? null)
            ? $bootstrap['items']
            : [];
        $languages = is_array($bootstrap['languages'] ?? null)
            ? $bootstrap['languages']
            : [];
        $defaultLangId = $this->resolveLanguageContext($languages)['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, ['label', 'custom_url'], $defaultLangId, 'translations', true)
            : [];

        return $this->render('cms/menus/items/edit', [
            'title'     => lang('Menus.menus_items_edit') ?? 'Edit Menu Item',
            'menuId'    => $menuId,
            'itemId'    => $itemId,
            'menu'      => is_array($bootstrap['menu'] ?? null) ? $bootstrap['menu'] : [],
            'item'      => $bootstrap['item'],
            'items'     => $items,
            'pages'     => is_array($bootstrap['pages'] ?? null) ? $this->pagesOptionsFromItems($bootstrap['pages']) : [],
            'entries'   => is_array($bootstrap['entries'] ?? null) ? $this->entriesOptionsFromItems($bootstrap['entries']) : [],
            'collections' => is_array($bootstrap['collections'] ?? null) ? $this->collectionsOptionsFromItems($bootstrap['collections']) : [],
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

    /**
     * @param array<int|string, mixed> $items
     * @return array<string, string>
     */
    private function pagesOptionsFromItems(array $items): array
    {
        $options = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = null;
            $translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            foreach ($translations as $translation) {
                if (is_array($translation) && ! empty($translation['title'])) {
                    $label = (string) $translation['title'];
                    break;
                }
            }
            $options[(string) $item['id']] = $label ?? (string) ($item['name'] ?? $item['title'] ?? $item['id']);
        }

        return $options;
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<string, string>
     */
    private function entriesOptionsFromItems(array $items): array
    {
        $options = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = null;
            $translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            foreach ($translations as $translation) {
                if (is_array($translation) && ! empty($translation['title'])) {
                    $label = (string) $translation['title'];
                    break;
                }
            }
            $options[(string) $item['id']] = $label ?? (string) ($item['title'] ?? $item['name'] ?? $item['slug'] ?? $item['id']);
        }

        return $options;
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<string, string>
     */
    private function collectionsOptionsFromItems(array $items): array
    {
        $options = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['id'])) {
                $options[(string) $item['id']] = (string) ($item['collection_key'] ?? $item['name'] ?? $item['title'] ?? $item['id']);
            }
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
        if (! has_permission('cms.menus.write')) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        if (! is_numeric($menuId) || (int) $menuId < 1) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'A menu scope is required.',
            ])->setStatusCode(400);
        }

        return $this->saveSortOrderFromJson(
            'cms',
            'menu_items',
            ['menu_id' => (int) $menuId],
            lang('Files.gallery_save_success') ?? 'Order saved successfully.',
        );
    }

}

<?php

declare(strict_types=1);

namespace App\Modules\Museum\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Museum\Requests\CollectionItemStoreRequest;
use App\Modules\Museum\Requests\CollectionItemUpdateRequest;
use App\Modules\Museum\Services\CollectionItemApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CollectionItemController extends BaseWebController
{
    protected CollectionItemApiServiceInterface $collectionItemService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->collectionItemService = service('museumCollectionItemApiService');
    }

    public function index(): string
    {
        return $this->render('museum/collection_items/index', [
            'title'        => lang('Museum.collection_items_title'),
            'limitOptions' => [10, 25, 50, 100],
            'categories' => $this->categoriesOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['category_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->collectionItemService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->collectionItemService->get($id));

        if (! $response['ok']) {
            return $this->render('museum/collection_items/show', [
                'title' => lang('Museum.collection_items_details'),
                'collectionItem' => [],
                'error' => $this->firstMessage($response, lang('Museum.collection_items_not_found')),
            'categories' => $this->categoriesOptions(),
            ]);
        }

        return $this->render('museum/collection_items/show', [
            'title' => lang('Museum.collection_items_details'),
            'collectionItem' => $this->extractData($response),
            'categories' => $this->categoriesOptions(),
        ]);
    }

    public function create(): string
    {
        return $this->render('museum/collection_items/create', [
            'title' => lang('Museum.collection_items_create'),
            'categories' => $this->categoriesOptions(),
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

        return redirect()->to(route_to('admin.museum.collection_items'))->with('success', lang('Museum.collection_items_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionItemService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Museum.collection_items_not_found'), route_to('admin.museum.collection_items'));
        }

        return $this->render('museum/collection_items/edit', [
            'title' => lang('Museum.collection_items_edit'),
            'item'  => $this->extractData($response),
            'categories' => $this->categoriesOptions(),
        ]);
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

        return redirect()->to(route_to('admin.museum.collection_items'))->with('success', lang('Museum.collection_items_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionItemService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.collection_items_delete_failed'), route_to('admin.museum.collection_items'), false);
        }

        return redirect()->to(route_to('admin.museum.collection_items'))->with('success', lang('Museum.collection_items_delete_success'));
    }





    /** @return array<string, string> */
    private function categoriesOptions(): array
    {
        $categoryService = service('museumCategoryApiService');
        $response = $this->safeApiCall(fn () => $categoryService->list(['per_page' => 100]));
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
}

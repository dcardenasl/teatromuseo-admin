<?php

declare(strict_types=1);

namespace App\Modules\Museum\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Museum\Requests\TechniqueStoreRequest;
use App\Modules\Museum\Requests\TechniqueUpdateRequest;
use App\Modules\Museum\Services\TechniqueApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TechniqueController extends BaseWebController
{
    protected TechniqueApiServiceInterface $techniqueService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->techniqueService = service('museumTechniqueApiService');
    }

    public function index(): string
    {
        return $this->render('museum/techniques/index', [
            'title'        => lang('Museum.techniques_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->techniqueService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->techniqueService->get($id));

        if (! $response['ok']) {
            return $this->render('museum/techniques/show', [
                'title' => lang('Museum.techniques_details'),
                'technique' => [],
                'error' => $this->firstMessage($response, lang('Museum.techniques_not_found')),

            ]);
        }

        return $this->render('museum/techniques/show', [
            'title' => lang('Museum.techniques_details'),
            'technique' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        return $this->render('museum/techniques/create', [
            'title' => lang('Museum.techniques_create'),

        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var TechniqueStoreRequest $request */
        $request = service('formRequest', TechniqueStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->techniqueService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.techniques_create_failed'));
        }

        return redirect()->to(route_to('admin.museum.techniques'))->with('success', lang('Museum.techniques_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->techniqueService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Museum.techniques_not_found'), route_to('admin.museum.techniques'));
        }

        return $this->render('museum/techniques/edit', [
            'title' => lang('Museum.techniques_edit'),
            'item'  => $this->extractData($response),

        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var TechniqueUpdateRequest $request */
        $request = service('formRequest', TechniqueUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->techniqueService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.techniques_update_failed'));
        }

        return redirect()->to(route_to('admin.museum.techniques'))->with('success', lang('Museum.techniques_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->techniqueService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.techniques_delete_failed'), route_to('admin.museum.techniques'), false);
        }

        return redirect()->to(route_to('admin.museum.techniques'))->with('success', lang('Museum.techniques_delete_success'));
    }



    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->techniqueService->list(['limit' => 250, 'sort' => 'sort_order']));
        $items = $this->extractItems($response);

        return $this->render('museum/techniques/reorder', [
            'title' => lang('Museum.techniques_title') . ' - ' . lang('Museum.field_sort_order'),
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
                $this->techniqueService->update($id, ['sort_order' => $value]);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved.',
        ]);
    }


    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('museum.write')) {
            return $this->withError(lang('App.no_permission'), route_to('admin.museum.techniques'));
        }
        return null;
    }
}

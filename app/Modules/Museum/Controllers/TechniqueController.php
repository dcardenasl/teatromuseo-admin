<?php

declare(strict_types=1);

namespace App\Modules\Museum\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Museum\Requests\TechniqueStoreRequest;
use App\Modules\Museum\Requests\TechniqueUpdateRequest;
use App\Modules\Museum\Services\CatalogTechniqueBffAdapter;
use App\Modules\Museum\Services\TechniqueApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TechniqueController extends BaseWebController
{
    protected TechniqueApiServiceInterface $techniqueService;
    protected CatalogTechniqueBffAdapter $workspaceAdapter;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->techniqueService = service('museumTechniqueApiService');
        $this->workspaceAdapter = service('catalogTechniqueBffAdapter');
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
            fn (array $params) => $this->techniqueService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $workspace = $this->workspaceAdapter->workspace((int) $id);
        $technique = is_array($workspace['technique'] ?? null) ? $workspace['technique'] : [];
        $languages = is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [];

        if ($technique === []) {
            return $this->render('museum/techniques/show', [
                'title'     => lang('Museum.techniques_details'),
                'technique' => [],
                'error'     => $this->workspaceAdapter->wasUnavailable()
                    ? lang('App.connection_error')
                    : lang('Museum.techniques_not_found'),
                'languages' => $languages,
            ]);
        }

        return $this->render('museum/techniques/show', [
            'title'     => lang('Museum.techniques_details'),
            'technique' => $technique,
            'languages' => $languages,
        ]);
    }

    public function create(): string
    {
        $workspace = $this->workspaceAdapter->workspace();
        $languages = $workspace !== null && is_array($workspace['languages'] ?? null)
            ? $workspace['languages']
            : [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && ! empty($languages))
            ? $this->buildTranslateTargets($languages, ['name', 'summary'], $defaultLangId)
            : [];

        return $this->render('museum/techniques/create', [
            'title'            => lang('Museum.techniques_create'),
            'languages'        => $languages,
            'defaultLangId'    => $defaultLangId,
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'translateTargets' => $translateTargets,
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

        $this->invalidatePublicSiteCache('techniques');

        return redirect()->to(route_to('admin.museum.techniques'))->with('success', lang('Museum.techniques_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $workspace = $this->workspaceAdapter->workspace((int) $id);
        $item = is_array($workspace['technique'] ?? null) ? $workspace['technique'] : [];
        if ($item === []) {
            return $this->withError(lang('Museum.techniques_not_found'), route_to('admin.museum.techniques'));
        }

        $languages = is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $translateTargets = ($defaultLangId > 0 && ! empty($languages))
            ? $this->buildTranslateTargets($languages, ['name', 'summary'], $defaultLangId)
            : [];

        return $this->render('museum/techniques/edit', [
            'title'            => lang('Museum.techniques_edit'),
            'item'             => $item,
            'languages'        => $languages,
            'defaultLangId'    => $defaultLangId,
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'translateTargets' => $translateTargets,
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

        $this->invalidatePublicSiteCache('techniques');

        return redirect()->to(route_to('admin.museum.techniques'))->with('success', lang('Museum.techniques_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->techniqueService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Museum.techniques_delete_failed'), route_to('admin.museum.techniques'), false);
        }

        $this->invalidatePublicSiteCache('techniques');

        return redirect()->to(route_to('admin.museum.techniques'))->with('success', lang('Museum.techniques_delete_success'));
    }

    public function reorder(): string
    {
        $response = $this->safeApiCall(fn () => $this->techniqueService->list(['limit' => 250, 'sort' => 'sort_order']));
        $items = $this->extractItems($response);

        return $this->render('museum/techniques/reorder', [
            'title' => lang('Museum.techniques_title') . ' - ' . lang('Museum.field_sort_order'),
            'items' => $items,
        ]);
    }

    public function saveOrder(): ResponseInterface
    {
        return $this->saveSortOrderFromJson(
            'catalog',
            'techniques',
            [],
            lang('Museum.sort_order_saved'),
        );
    }

}

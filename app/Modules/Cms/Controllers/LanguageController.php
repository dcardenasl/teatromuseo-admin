<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\LanguageStoreRequest;
use App\Modules\Cms\Requests\LanguageUpdateRequest;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class LanguageController extends BaseWebController
{
    protected LanguageApiService $languageService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->languageService = service('languageApiService');
    }

    private function requireRead(): ?RedirectResponse
    {
        if (! has_permission('cms.languages.read')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.languages.write')) {
            return redirect()->to(route_to('admin.cms.languages'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function index(): string|RedirectResponse
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $deny;
        }

        return $this->render('cms/languages/index', [
            'title'        => lang('CmsLanguages.languages_title'),
            'limitOptions' => [10, 25, 50, 100],
        ]);
    }

    public function data(): ResponseInterface
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->languageService->list($params),
        );
    }

    public function show(string $id): string|RedirectResponse
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/languages/show', [
                'title' => lang('CmsLanguages.languages_details'),
                'language' => [],
                'error' => $this->firstMessage($response, lang('CmsLanguages.languages_not_found')),
            ]);
        }

        return $this->render('cms/languages/show', [
            'title' => lang('CmsLanguages.languages_details'),
            'language' => $this->extractData($response),
        ]);
    }

    public function create(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->list(['limit' => 250]));
        $languages = $this->extractItems($response);

        return $this->render('cms/languages/create', [
            'title' => lang('CmsLanguages.languages_create'),
            'languages' => $languages,
        ]);
    }

    public function store(): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        /** @var LanguageStoreRequest $request */
        $request = service('formRequest', LanguageStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('CmsLanguages.languages_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.languages'))->with('success', lang('CmsLanguages.languages_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('CmsLanguages.languages_not_found'), route_to('admin.cms.languages'));
        }

        $languagesResponse = $this->safeApiCall(fn () => $this->languageService->list(['limit' => 250]));
        $languages = $this->extractItems($languagesResponse);

        // Exclude current language to avoid circular fallbacks
        $languages = array_filter($languages, static fn ($lang) => (string) ($lang['id'] ?? '') !== $id);

        return $this->render('cms/languages/edit', [
            'title' => lang('CmsLanguages.languages_edit'),
            'item'  => $this->extractData($response),
            'languages' => $languages,
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        /** @var LanguageUpdateRequest $request */
        $request = service('formRequest', LanguageUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('CmsLanguages.languages_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.languages'))->with('success', lang('CmsLanguages.languages_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('CmsLanguages.languages_delete_failed'), route_to('admin.cms.languages'), false);
        }

        return redirect()->to(route_to('admin.cms.languages'))->with('success', lang('CmsLanguages.languages_delete_success'));
    }

    public function setDefault(string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->update($id, ['is_default' => true]));

        if (! $response['ok']) {
            return $this->failApi($response, lang('CmsLanguages.languages_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.languages'))->with('success', lang('CmsLanguages.languages_update_success'));
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->languageService->list(['limit' => 250, 'sort' => 'sort_order']));
        $items = $this->extractItems($response);

        return $this->render('cms/languages/reorder', [
            'title' => lang('CmsLanguages.languages_title') . ' - ' . lang('CmsLanguages.field_sort_order'),
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
                $response = $this->safeApiCall(fn () => $this->languageService->update($id, ['sort_order' => $value]));

                if (! $response['ok']) {
                    return $this->response->setJSON([
                        'ok' => false,
                        'message' => $this->firstMessage($response, lang('CmsLanguages.languages_update_failed')),
                    ])->setStatusCode(422);
                }
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved.',
        ]);
    }
}

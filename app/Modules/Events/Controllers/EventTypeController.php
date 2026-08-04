<?php

declare(strict_types=1);

namespace App\Modules\Events\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Events\Requests\EventTypeStoreRequest;
use App\Modules\Events\Requests\EventTypeUpdateRequest;
use App\Modules\Events\Services\EventTypeApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

final class EventTypeController extends BaseWebController
{
    protected EventTypeApiServiceInterface $eventTypeService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->eventTypeService = service('eventTypeApiService');
    }

    public function index(): string
    {
        return $this->render('events/event_types/index', [
            'title' => lang('Events.event_types_title'),
            'limitOptions' => [10, 25, 50, 100],
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse([], ['created_at', 'is_active'], fn (array $params) => $this->eventTypeService->list($params));
    }

    public function checkSlug(): ResponseInterface
    {
        $slugValue = $this->request->getGet('slug');
        $localeValue = $this->request->getGet('locale');
        $currentIdValue = $this->request->getGet('current_id');
        $slug = is_scalar($slugValue) ? (string) $slugValue : '';
        $locale = is_scalar($localeValue) ? (string) $localeValue : '';
        $currentId = is_scalar($currentIdValue) ? (string) $currentIdValue : '';
        $response = $this->safeApiCall(fn () => $this->eventTypeService->checkSlug($slug, $locale, $currentId));
        $data = $this->extractData($response);

        return $this->response->setJSON(['available' => (bool) ($data['available'] ?? false)]);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        return $this->render('events/event_types/create', [
            'title' => lang('Events.event_types_create'),
            'languages' => $languages,
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'translateTargets' => $this->buildTranslateTargets($languages, ['name'], $languageContext['defaultLangId']),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var EventTypeStoreRequest $request */
        $request = service('formRequest', EventTypeStoreRequest::class, false);
        if (($invalid = $this->validateRequest($request)) !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventTypeService->create($request->payload()));
        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.event_types_create_failed'));
        }

        $this->invalidatePublicSiteCache('event_types');

        return redirect()->to(route_to('admin.events.event_types'))->with('success', lang('Events.event_types_create_success'));
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->eventTypeService->get($id));
        $languages = $this->getLanguages();

        return $this->render('events/event_types/show', [
            'title' => lang('Events.event_types_details'),
            'eventType' => $response['ok'] ? $this->extractData($response) : [],
            'error' => $response['ok'] ? null : $this->firstMessage($response, lang('Events.event_types_not_found')),
            'languages' => $languages,
        ]);
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventTypeService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Events.event_types_not_found'), route_to('admin.events.event_types'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        return $this->render('events/event_types/edit', [
            'title' => lang('Events.event_types_edit'),
            'item' => $this->extractData($response),
            'languages' => $languages,
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'translateTargets' => $this->buildTranslateTargets($languages, ['name'], $languageContext['defaultLangId']),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var EventTypeUpdateRequest $request */
        $request = service('formRequest', EventTypeUpdateRequest::class, false);
        if (($invalid = $this->validateRequest($request)) !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventTypeService->update($id, $request->payload()));
        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.event_types_update_failed'));
        }

        $this->invalidatePublicSiteCache('event_types');

        return redirect()->to(route_to('admin.events.event_types'))->with('success', lang('Events.event_types_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventTypeService->delete($id));
        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.event_types_delete_failed'), route_to('admin.events.event_types'), false);
        }

        $this->invalidatePublicSiteCache('event_types');

        return redirect()->to(route_to('admin.events.event_types'))->with('success', lang('Events.event_types_delete_success'));
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        // The event-domain contract caps `per_page` at 100; keep this aligned
        // with the shared table response contract instead of sending `limit`.
        $response = $this->safeApiCall(fn () => $this->eventTypeService->list([
            'per_page' => 100,
            'page' => 1,
            'sort' => 'sort_order',
        ]));

        return $this->render('events/event_types/reorder', [
            'title' => lang('Events.event_types_title') . ' - ' . lang('Events.field_sort_order'),
            'items' => $this->extractItems($response),
        ]);
    }

    public function saveOrder(): ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $this->response->setJSON(['ok' => false, 'message' => lang('App.access_denied')])->setStatusCode(403);
        }

        $request = $this->request;
        if (! $request instanceof \CodeIgniter\HTTP\IncomingRequest) {
            return $this->response->setJSON(['ok' => false, 'message' => lang('App.invalid_request')])->setStatusCode(400);
        }

        $json = $request->getJSON(true);
        $items = is_array($json) && is_array($json['items'] ?? null) ? $json['items'] : null;
        if ($items === null) {
            return $this->response->setJSON(['ok' => false, 'message' => lang('App.invalid_request')])->setStatusCode(400);
        }

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            $this->eventTypeService->update((string) $item['id'], [
                'sort_order' => (int) ($item['sort_order'] ?? 0),
            ]);
        }

        $this->invalidatePublicSiteCache('event_types');

        return $this->response->setJSON(['ok' => true, 'message' => lang('Events.event_types_order_saved')]);
    }

    /** @return array<string, mixed> */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));

        return $response['ok'] ? $this->extractItems($response) : [];
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('event.event-types.write')) {
            return $this->withError(lang('App.no_permission'), route_to('admin.events.event_types'));
        }

        return null;
    }
}

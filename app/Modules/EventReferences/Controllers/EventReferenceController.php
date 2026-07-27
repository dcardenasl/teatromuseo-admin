<?php

declare(strict_types=1);

namespace App\Modules\EventReferences\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\EventReferences\Requests\EventReferenceStoreRequest;
use App\Modules\EventReferences\Requests\EventReferenceUpdateRequest;
use App\Modules\EventReferences\Services\EventReferenceApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EventReferenceController extends BaseWebController
{
    protected EventReferenceApiServiceInterface $eventReferenceService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->eventReferenceService = service('eventReferenceApiService');
    }

    public function index(): string
    {
        return $this->render('eventreferences/event_references/index', [
            'title'        => lang('EventReferences.event_references_title'),
            'limitOptions' => [10, 25, 50, 100],
            'events' => $this->eventsOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['event_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->eventReferenceService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->eventReferenceService->get($id));

        if (! $response['ok']) {
            return $this->render('eventreferences/event_references/show', [
                'title' => lang('EventReferences.event_references_details'),
                'eventReference' => [],
                'error' => $this->firstMessage($response, lang('EventReferences.event_references_not_found')),
            'events' => $this->eventsOptions(),
            ]);
        }

        return $this->render('eventreferences/event_references/show', [
            'title' => lang('EventReferences.event_references_details'),
            'eventReference' => $this->extractData($response),
            'events' => $this->eventsOptions(),
        ]);
    }

    public function create(): string
    {
        return $this->render('eventreferences/event_references/create', [
            'title' => lang('EventReferences.event_references_create'),
            'events' => $this->eventsOptions(),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var EventReferenceStoreRequest $request */
        $request = service('formRequest', EventReferenceStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventReferenceService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('EventReferences.event_references_create_failed'));
        }

        return redirect()->to(route_to('admin.eventreferences.event_references'))->with('success', lang('EventReferences.event_references_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventReferenceService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('EventReferences.event_references_not_found'), route_to('admin.eventreferences.event_references'));
        }

        return $this->render('eventreferences/event_references/edit', [
            'title' => lang('EventReferences.event_references_edit'),
            'item'  => $this->extractData($response),
            'events' => $this->eventsOptions(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var EventReferenceUpdateRequest $request */
        $request = service('formRequest', EventReferenceUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventReferenceService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('EventReferences.event_references_update_failed'));
        }

        return redirect()->to(route_to('admin.eventreferences.event_references'))->with('success', lang('EventReferences.event_references_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventReferenceService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('EventReferences.event_references_delete_failed'), route_to('admin.eventreferences.event_references'), false);
        }

        return redirect()->to(route_to('admin.eventreferences.event_references'))->with('success', lang('EventReferences.event_references_delete_success'));
    }





    /** @return array<string, string> */
    private function eventsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->eventReferenceService->events(['limit' => 100]));
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

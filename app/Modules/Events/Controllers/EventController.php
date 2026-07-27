<?php

declare(strict_types=1);

namespace App\Modules\Events\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Events\Requests\EventStoreRequest;
use App\Modules\Events\Requests\EventUpdateRequest;
use App\Modules\Events\Services\EventApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EventController extends BaseWebController
{
    protected EventApiServiceInterface $eventService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->eventService = service('eventApiService');
    }

    public function index(): string
    {
        return $this->render('events/events/index', [
            'title'        => lang('Events.events_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->eventService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->eventService->get($id));

        if (! $response['ok']) {
            return $this->render('events/events/show', [
                'title' => lang('Events.events_details'),
                'event' => [],
                'error' => $this->firstMessage($response, lang('Events.events_not_found')),

            ]);
        }

        return $this->render('events/events/show', [
            'title' => lang('Events.events_details'),
            'event' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        return $this->render('events/events/create', [
            'title' => lang('Events.events_create'),

        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var EventStoreRequest $request */
        $request = service('formRequest', EventStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.events_create_failed'));
        }

        return redirect()->to(route_to('admin.events.events'))->with('success', lang('Events.events_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Events.events_not_found'), route_to('admin.events.events'));
        }

        return $this->render('events/events/edit', [
            'title' => lang('Events.events_edit'),
            'item'  => $this->extractData($response),

        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var EventUpdateRequest $request */
        $request = service('formRequest', EventUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.events_update_failed'));
        }

        return redirect()->to(route_to('admin.events.events'))->with('success', lang('Events.events_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.events_delete_failed'), route_to('admin.events.events'), false);
        }

        return redirect()->to(route_to('admin.events.events'))->with('success', lang('Events.events_delete_success'));
    }




}

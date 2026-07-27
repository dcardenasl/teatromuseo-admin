<?php

declare(strict_types=1);

namespace App\Modules\TicketTypes\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\TicketTypes\Requests\TicketTypeStoreRequest;
use App\Modules\TicketTypes\Requests\TicketTypeUpdateRequest;
use App\Modules\TicketTypes\Services\TicketTypeApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TicketTypeController extends BaseWebController
{
    protected TicketTypeApiServiceInterface $ticketTypeService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->ticketTypeService = service('ticketTypeApiService');
    }

    public function index(): string
    {
        return $this->render('tickettypes/ticket_types/index', [
            'title'        => lang('TicketTypes.ticket_types_title'),
            'limitOptions' => [10, 25, 50, 100],
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['event_id', 'occurrence_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->ticketTypeService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->ticketTypeService->get($id));

        if (! $response['ok']) {
            return $this->render('tickettypes/ticket_types/show', [
                'title' => lang('TicketTypes.ticket_types_details'),
                'ticketType' => [],
                'error' => $this->firstMessage($response, lang('TicketTypes.ticket_types_not_found')),
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
            ]);
        }

        return $this->render('tickettypes/ticket_types/show', [
            'title' => lang('TicketTypes.ticket_types_details'),
            'ticketType' => $this->extractData($response),
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
        ]);
    }

    public function create(): string
    {
        return $this->render('tickettypes/ticket_types/create', [
            'title' => lang('TicketTypes.ticket_types_create'),
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var TicketTypeStoreRequest $request */
        $request = service('formRequest', TicketTypeStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->ticketTypeService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('TicketTypes.ticket_types_create_failed'));
        }

        return redirect()->to(route_to('admin.tickettypes.ticket_types'))->with('success', lang('TicketTypes.ticket_types_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->ticketTypeService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('TicketTypes.ticket_types_not_found'), route_to('admin.tickettypes.ticket_types'));
        }

        return $this->render('tickettypes/ticket_types/edit', [
            'title' => lang('TicketTypes.ticket_types_edit'),
            'item'  => $this->extractData($response),
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var TicketTypeUpdateRequest $request */
        $request = service('formRequest', TicketTypeUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->ticketTypeService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('TicketTypes.ticket_types_update_failed'));
        }

        return redirect()->to(route_to('admin.tickettypes.ticket_types'))->with('success', lang('TicketTypes.ticket_types_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->ticketTypeService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('TicketTypes.ticket_types_delete_failed'), route_to('admin.tickettypes.ticket_types'), false);
        }

        return redirect()->to(route_to('admin.tickettypes.ticket_types'))->with('success', lang('TicketTypes.ticket_types_delete_success'));
    }





    /** @return array<string, string> */
    private function eventsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->ticketTypeService->events(['limit' => 100]));
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

    /** @return array<string, string> */
    private function occurrencesOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->ticketTypeService->occurrences(['limit' => 100]));
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

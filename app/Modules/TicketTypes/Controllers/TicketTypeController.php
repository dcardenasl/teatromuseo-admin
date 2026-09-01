<?php

declare(strict_types=1);

namespace App\Modules\TicketTypes\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Events\Services\EventLookupBffAdapter;
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
    protected EventLookupBffAdapter $eventLookupAdapter;
    /** @var array<string, mixed>|null */
    private ?array $lookupResponse = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->ticketTypeService = service('ticketTypeApiService');
        $this->eventLookupAdapter = service('eventLookupBffAdapter');
    }

    public function index(): string
    {
        return $this->render('tickettypes/ticket_types/index', [
            'title'        => lang('TicketTypes.ticket_types_title'),
            'limitOptions' => [10, 25, 50, 100],
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
            'lookupAvailable' => $this->lookupAvailable(),
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
            'lookupAvailable' => $this->lookupAvailable(),
            ]);
        }

        return $this->render('tickettypes/ticket_types/show', [
            'title' => lang('TicketTypes.ticket_types_details'),
            'ticketType' => $this->extractData($response),
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
            'lookupAvailable' => $this->lookupAvailable(),
        ]);
    }

    public function create(): string
    {
        return $this->render('tickettypes/ticket_types/create', [
            'title' => lang('TicketTypes.ticket_types_create'),
            'events' => $this->eventsOptions(),
            'occurrences' => $this->occurrencesOptions(),
            'lookupAvailable' => $this->lookupAvailable(),
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
            'lookupAvailable' => $this->lookupAvailable(),
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
        $response = $this->lookupResponse();
        $options = [];

        foreach ($this->eventLookupAdapter->section($response, 'events') as $item) {
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
        $response = $this->lookupResponse();
        $options = [];

        foreach ($this->eventLookupAdapter->section($response, 'occurrences') as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $eventLabel = $item['event_title'] ?? $item['event_name'] ?? null;
            $startTime = $item['start_time'] ?? null;
            $label = is_string($eventLabel) && trim($eventLabel) !== '' ? trim($eventLabel) : lang('Occurrences.occurrences_title');
            if (is_string($startTime) && trim($startTime) !== '') {
                try {
                    $label .= ' · ' . (new \DateTimeImmutable(
                        $startTime,
                        new \DateTimeZone((string) env('EVENT_SCHEDULE_TIMEZONE', 'America/Santiago')),
                    ))->format('d/m/Y H:i');
                } catch (\Throwable) {
                    $label .= ' · ' . $startTime;
                }
            }
            if ($label === lang('Occurrences.occurrences_title') && trim((string) $startTime) === '') {
                $label .= ' · ' . (string) $item['id'];
            }
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /** @return array<string, mixed> */
    private function lookupResponse(): array
    {
        return $this->lookupResponse ??= $this->safeApiCall(
            fn (): array => $this->eventLookupAdapter->read('ticket_type'),
        );
    }

    private function lookupAvailable(): bool
    {
        return $this->eventLookupAdapter->sourceAvailable($this->lookupResponse());
    }
}

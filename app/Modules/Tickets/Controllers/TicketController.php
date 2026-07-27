<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Tickets\Requests\TicketStoreRequest;
use App\Modules\Tickets\Requests\TicketUpdateRequest;
use App\Modules\Tickets\Services\TicketApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TicketController extends BaseWebController
{
    protected TicketApiServiceInterface $ticketService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->ticketService = service('ticketApiService');
    }

    public function index(): string
    {
        return $this->render('tickets/tickets/index', [
            'title'        => lang('Tickets.tickets_title'),
            'limitOptions' => [10, 25, 50, 100],
            'bookings' => $this->bookingsOptions(),
            'ticketTypes' => $this->ticketTypesOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['booking_id', 'ticket_type_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->ticketService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->ticketService->get($id));

        if (! $response['ok']) {
            return $this->render('tickets/tickets/show', [
                'title' => lang('Tickets.tickets_details'),
                'ticket' => [],
                'error' => $this->firstMessage($response, lang('Tickets.tickets_not_found')),
            'bookings' => $this->bookingsOptions(),
            'ticketTypes' => $this->ticketTypesOptions(),
            ]);
        }

        return $this->render('tickets/tickets/show', [
            'title' => lang('Tickets.tickets_details'),
            'ticket' => $this->extractData($response),
            'bookings' => $this->bookingsOptions(),
            'ticketTypes' => $this->ticketTypesOptions(),
        ]);
    }

    public function create(): string
    {
        return $this->render('tickets/tickets/create', [
            'title' => lang('Tickets.tickets_create'),
            'bookings' => $this->bookingsOptions(),
            'ticketTypes' => $this->ticketTypesOptions(),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var TicketStoreRequest $request */
        $request = service('formRequest', TicketStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->ticketService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Tickets.tickets_create_failed'));
        }

        return redirect()->to(route_to('admin.tickets.tickets'))->with('success', lang('Tickets.tickets_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->ticketService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Tickets.tickets_not_found'), route_to('admin.tickets.tickets'));
        }

        return $this->render('tickets/tickets/edit', [
            'title' => lang('Tickets.tickets_edit'),
            'item'  => $this->extractData($response),
            'bookings' => $this->bookingsOptions(),
            'ticketTypes' => $this->ticketTypesOptions(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var TicketUpdateRequest $request */
        $request = service('formRequest', TicketUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->ticketService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Tickets.tickets_update_failed'));
        }

        return redirect()->to(route_to('admin.tickets.tickets'))->with('success', lang('Tickets.tickets_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->ticketService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Tickets.tickets_delete_failed'), route_to('admin.tickets.tickets'), false);
        }

        return redirect()->to(route_to('admin.tickets.tickets'))->with('success', lang('Tickets.tickets_delete_success'));
    }





    /** @return array<string, string> */
    private function bookingsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->ticketService->bookings(['limit' => 100]));
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $guestEmail = $item['guest_email'] ?? $item['email'] ?? null;
            $label = is_string($guestEmail) && trim($guestEmail) !== ''
                ? lang('Bookings.bookings_title') . ' · ' . trim($guestEmail)
                : ($item['name'] ?? $item['title'] ?? $item['label'] ?? $item['id']);
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /** @return array<string, string> */
    private function ticketTypesOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->ticketService->ticketTypes(['limit' => 100]));
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

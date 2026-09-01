<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Bookings\Requests\BookingStoreRequest;
use App\Modules\Bookings\Requests\BookingUpdateRequest;
use App\Modules\Bookings\Services\BookingApiServiceInterface;
use App\Modules\Events\Services\EventLookupBffAdapter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BookingController extends BaseWebController
{
    protected BookingApiServiceInterface $bookingService;
    protected EventLookupBffAdapter $eventLookupAdapter;
    /** @var array<string, mixed>|null */
    private ?array $lookupResponse = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->bookingService = service('bookingApiService');
        $this->eventLookupAdapter = service('eventLookupBffAdapter');
    }

    public function index(): string
    {
        return $this->render('bookings/bookings/index', [
            'title'        => lang('Bookings.bookings_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->bookingService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->bookingService->get($id));

        if (! $response['ok']) {
            return $this->render('bookings/bookings/show', [
                'title' => lang('Bookings.bookings_details'),
                'booking' => [],
                'error' => $this->firstMessage($response, lang('Bookings.bookings_not_found')),

            ]);
        }

        return $this->render('bookings/bookings/show', [
            'title' => lang('Bookings.bookings_details'),
            'booking' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        return $this->render('bookings/bookings/create', [
            'title' => lang('Bookings.bookings_create'),
            'ticketTypes' => $this->ticketTypesOptions(),
            'lookupAvailable' => $this->lookupAvailable(),

        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var BookingStoreRequest $request */
        $request = service('formRequest', BookingStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->bookingService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Bookings.bookings_create_failed'));
        }

        return redirect()->to(route_to('admin.bookings.bookings'))->with('success', lang('Bookings.bookings_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->bookingService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Bookings.bookings_not_found'), route_to('admin.bookings.bookings'));
        }

        return $this->render('bookings/bookings/edit', [
            'title' => lang('Bookings.bookings_edit'),
            'item'  => $this->extractData($response),
            'lookupAvailable' => $this->lookupAvailable(),

        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var BookingUpdateRequest $request */
        $request = service('formRequest', BookingUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->bookingService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Bookings.bookings_update_failed'));
        }

        return redirect()->to(route_to('admin.bookings.bookings'))->with('success', lang('Bookings.bookings_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->bookingService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Bookings.bookings_delete_failed'), route_to('admin.bookings.bookings'), false);
        }

        return redirect()->to(route_to('admin.bookings.bookings'))->with('success', lang('Bookings.bookings_delete_success'));
    }

    /** @return array<string, string> */
    private function ticketTypesOptions(): array
    {
        $response = $this->lookupResponse();
        $options = [];

        foreach ($this->eventLookupAdapter->section($response, 'ticket_types') as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            $label = $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /** @return array<string, mixed> */
    private function lookupResponse(): array
    {
        return $this->lookupResponse ??= $this->safeApiCall(
            fn (): array => $this->eventLookupAdapter->read('booking'),
        );
    }

    private function lookupAvailable(): bool
    {
        return $this->eventLookupAdapter->sourceAvailable($this->lookupResponse());
    }




}

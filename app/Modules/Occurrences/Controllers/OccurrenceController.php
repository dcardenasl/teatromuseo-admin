<?php

declare(strict_types=1);

namespace App\Modules\Occurrences\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Events\Services\EventLookupBffAdapter;
use App\Modules\Occurrences\Requests\OccurrenceStoreRequest;
use App\Modules\Occurrences\Requests\OccurrenceUpdateRequest;
use App\Modules\Occurrences\Services\OccurrenceApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class OccurrenceController extends BaseWebController
{
    protected OccurrenceApiServiceInterface $occurrenceService;
    protected EventLookupBffAdapter $eventLookupAdapter;
    /** @var array<string, mixed>|null */
    private ?array $lookupResponse = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->occurrenceService = service('occurrenceApiService');
        $this->eventLookupAdapter = service('eventLookupBffAdapter');
    }

    public function index(): string
    {
        return $this->render('occurrences/occurrences/index', [
            'title'        => lang('Occurrences.occurrences_title'),
            'limitOptions' => [10, 25, 50, 100],
            'events' => $this->eventsOptions(),
            'venues' => $this->venuesOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['event_id', 'venue_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->occurrenceService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->occurrenceService->get($id));

        if (! $response['ok']) {
            return $this->render('occurrences/occurrences/show', [
                'title' => lang('Occurrences.occurrences_details'),
                'occurrence' => [],
                'error' => $this->firstMessage($response, lang('Occurrences.occurrences_not_found')),
            'events' => $this->eventsOptions(),
            'venues' => $this->venuesOptions(),
            ]);
        }

        return $this->render('occurrences/occurrences/show', [
            'title' => lang('Occurrences.occurrences_details'),
            'occurrence' => $this->extractData($response),
            'events' => $this->eventsOptions(),
            'venues' => $this->venuesOptions(),
        ]);
    }

    public function create(): string
    {
        return $this->render('occurrences/occurrences/create', [
            'title' => lang('Occurrences.occurrences_create'),
            'events' => $this->eventsOptions(),
            'venues' => $this->venuesOptions(),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var OccurrenceStoreRequest $request */
        $request = service('formRequest', OccurrenceStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->occurrenceService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Occurrences.occurrences_create_failed'));
        }

        return redirect()->to(route_to('admin.occurrences.occurrences'))->with('success', lang('Occurrences.occurrences_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->occurrenceService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Occurrences.occurrences_not_found'), route_to('admin.occurrences.occurrences'));
        }

        return $this->render('occurrences/occurrences/edit', [
            'title' => lang('Occurrences.occurrences_edit'),
            'item'  => $this->extractData($response),
            'events' => $this->eventsOptions(),
            'venues' => $this->venuesOptions(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var OccurrenceUpdateRequest $request */
        $request = service('formRequest', OccurrenceUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->occurrenceService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Occurrences.occurrences_update_failed'));
        }

        return redirect()->to(route_to('admin.occurrences.occurrences'))->with('success', lang('Occurrences.occurrences_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->occurrenceService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Occurrences.occurrences_delete_failed'), route_to('admin.occurrences.occurrences'), false);
        }

        return redirect()->to(route_to('admin.occurrences.occurrences'))->with('success', lang('Occurrences.occurrences_delete_success'));
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
    private function venuesOptions(): array
    {
        $response = $this->lookupResponse();
        $options = [];

        foreach ($this->eventLookupAdapter->section($response, 'venues') as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['email'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    /** @return array<string, mixed> */
    private function lookupResponse(): array
    {
        return $this->lookupResponse ??= $this->safeApiCall(
            fn (): array => $this->eventLookupAdapter->read('occurrence'),
        );
    }
}

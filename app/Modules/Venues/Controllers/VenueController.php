<?php

declare(strict_types=1);

namespace App\Modules\Venues\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Venues\Requests\VenueStoreRequest;
use App\Modules\Venues\Requests\VenueUpdateRequest;
use App\Modules\Venues\Services\VenueApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class VenueController extends BaseWebController
{
    protected VenueApiServiceInterface $venueService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->venueService = service('venueApiService');
    }

    public function index(): string
    {
        return $this->render('venues/venues/index', [
            'title'        => lang('Venues.venues_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->venueService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->venueService->get($id));

        if (! $response['ok']) {
            return $this->render('venues/venues/show', [
                'title' => lang('Venues.venues_details'),
                'venue' => [],
                'error' => $this->firstMessage($response, lang('Venues.venues_not_found')),

            ]);
        }

        return $this->render('venues/venues/show', [
            'title' => lang('Venues.venues_details'),
            'venue' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        return $this->render('venues/venues/create', [
            'title' => lang('Venues.venues_create'),

        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var VenueStoreRequest $request */
        $request = service('formRequest', VenueStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->venueService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Venues.venues_create_failed'));
        }

        return redirect()->to(route_to('admin.venues.venues'))->with('success', lang('Venues.venues_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->venueService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Venues.venues_not_found'), route_to('admin.venues.venues'));
        }

        return $this->render('venues/venues/edit', [
            'title' => lang('Venues.venues_edit'),
            'item'  => $this->extractData($response),

        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var VenueUpdateRequest $request */
        $request = service('formRequest', VenueUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->venueService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Venues.venues_update_failed'));
        }

        return redirect()->to(route_to('admin.venues.venues'))->with('success', lang('Venues.venues_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->venueService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Venues.venues_delete_failed'), route_to('admin.venues.venues'), false);
        }

        return redirect()->to(route_to('admin.venues.venues'))->with('success', lang('Venues.venues_delete_success'));
    }




}

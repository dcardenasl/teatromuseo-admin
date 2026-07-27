<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Services;

use App\Services\ResourceApiService;

class BookingApiService extends ResourceApiService implements BookingApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/bookings';
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function ticketTypes(array $filters = []): array
    {
        return $this->apiClient->get('/events/ticket-types', $filters);
    }



}

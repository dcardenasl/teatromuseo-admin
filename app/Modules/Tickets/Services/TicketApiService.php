<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Services;

use App\Services\ResourceApiService;

class TicketApiService extends ResourceApiService implements TicketApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/tickets';
    }




    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function bookings(array $filters = []): array
    {
        return $this->apiClient->get('/events/bookings', $filters);
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

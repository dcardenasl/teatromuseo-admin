<?php

declare(strict_types=1);

namespace App\Modules\TicketTypes\Services;

use App\Services\ResourceApiService;

class TicketTypeApiService extends ResourceApiService implements TicketTypeApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/ticket-types';
    }




    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function events(array $filters = []): array
    {
        return $this->apiClient->get('/events/events', $filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function occurrences(array $filters = []): array
    {
        return $this->apiClient->get('/events/occurrences', $filters);
    }
}

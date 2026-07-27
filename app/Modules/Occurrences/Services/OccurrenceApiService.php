<?php

declare(strict_types=1);

namespace App\Modules\Occurrences\Services;

use App\Services\ResourceApiService;

class OccurrenceApiService extends ResourceApiService implements OccurrenceApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/occurrences';
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
    public function venues(array $filters = []): array
    {
        return $this->apiClient->get('/events/venues', $filters);
    }
}

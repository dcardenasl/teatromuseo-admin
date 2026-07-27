<?php

declare(strict_types=1);

namespace App\Modules\EventReferences\Services;

use App\Services\ResourceApiService;

class EventReferenceApiService extends ResourceApiService implements EventReferenceApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/event-references';
    }




    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function events(array $filters = []): array
    {
        return $this->apiClient->get('/events/events', $filters);
    }
}

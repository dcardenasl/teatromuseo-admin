<?php

declare(strict_types=1);

namespace App\Modules\Events\Services;

use App\Services\ResourceApiService;

class EventApiService extends ResourceApiService implements EventApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/events';
    }

    /** @return array<string, mixed> */
    public function listTypes(): array
    {
        return $this->apiClient->get('/events/event-types', [
            'per_page' => 100,
            'sort' => 'sort_order',
            'filter' => ['is_active' => '1'],
        ]);
    }



}

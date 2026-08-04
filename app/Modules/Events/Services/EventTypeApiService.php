<?php

declare(strict_types=1);

namespace App\Modules\Events\Services;

use App\Services\ResourceApiService;

final class EventTypeApiService extends ResourceApiService implements EventTypeApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/event-types';
    }

    /** @return array<string, mixed> */
    public function checkSlug(string $slug, string $locale, string $currentId = ''): array
    {
        return $this->apiClient->get($this->resourcePath() . '/check-slug', [
            'slug' => $slug,
            'locale' => $locale,
            'current_id' => $currentId,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Museum\Services;

use App\Services\ResourceApiService;

class CollectionItemApiService extends ResourceApiService implements CollectionItemApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/catalog/collection-items';
    }




    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function categories(array $filters = []): array
    {
        return $this->apiClient->get('/catalog/categories', $filters);
    }
}

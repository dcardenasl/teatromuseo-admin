<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\DomainApiClientInterface;

/**
 * Single transport seam for all Admin reorder operations.
 *
 * Each call is one request to the owning domain. The Admin controllers do not
 * retain a per-item update path for reorder actions.
 */
final class SortOrderApiService implements SortOrderApiServiceInterface
{
    public function __construct(
        private DomainApiClientInterface $cmsClient,
        private DomainApiClientInterface $catalogClient,
        private DomainApiClientInterface $eventClient,
    ) {
    }

    /** @param list<array{id: int|string, sort_order: int}> $items */
    public function cms(string $resource, array $items, array $scope = []): array
    {
        return $this->cmsClient->post('/cms/sort-orders', [
            'resource' => $resource,
            'items' => $items,
            'scope' => $scope,
        ]);
    }

    /** @param list<array{id: int|string, sort_order: int}> $items */
    public function catalog(string $resource, array $items): array
    {
        return $this->catalogClient->post('/catalog/sort-orders', [
            'resource' => $resource,
            'items' => $items,
        ]);
    }

    /** @param list<array{id: int|string, sort_order: int}> $items */
    public function events(string $resource, array $items): array
    {
        return $this->eventClient->post('/events/sort-orders', [
            'resource' => $resource,
            'items' => $items,
        ]);
    }
}

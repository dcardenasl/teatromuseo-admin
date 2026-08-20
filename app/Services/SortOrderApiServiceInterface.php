<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\ApiClientInterface;

/**
 * @phpstan-import-type ApiResponse from ApiClientInterface
 */
interface SortOrderApiServiceInterface
{
    /**
     * @param list<array{id: int|string, sort_order: int}> $items
     * @param array<string, int|string|null> $scope
     * @return ApiResponse
     */
    public function cms(string $resource, array $items, array $scope = []): array;

    /**
     * @param list<array{id: int|string, sort_order: int}> $items
     * @return ApiResponse
     */
    public function catalog(string $resource, array $items): array;

    /**
     * @param list<array{id: int|string, sort_order: int}> $items
     * @return ApiResponse
     */
    public function events(string $resource, array $items): array;
}

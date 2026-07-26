<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class MenuApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/menus';
    }

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function listItems(array $filters = []): array
    {
        return $this->apiClient->get('/cms/menu-items', $filters);
    }

    /** @return ApiResponse */
    public function getItem(int|string $id): array
    {
        return $this->apiClient->get('/cms/menu-items/' . $id);
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function createItem(array $payload): array
    {
        return $this->apiClient->post('/cms/menu-items', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function updateItem(int|string $id, array $payload): array
    {
        return $this->apiClient->put('/cms/menu-items/' . $id, $payload);
    }

    /** @return ApiResponse */
    public function deleteItem(int|string $id): array
    {
        return $this->apiClient->delete('/cms/menu-items/' . $id);
    }



}

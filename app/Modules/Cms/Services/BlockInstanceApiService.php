<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class BlockInstanceApiService extends BaseApiService
{
    private function buildPath(string|int $ownerId, string $ownerType): string
    {
        $type = $ownerType === 'entry' ? 'entries' : 'pages';
        return "/cms/{$type}/{$ownerId}/blocks";
    }

    /**
     * @param string|int $ownerId
     * @param string $ownerType 'page' or 'entry'
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function list(string|int $ownerId, string $ownerType, array $filters = []): array
    {
        return $this->apiClient->get($this->buildPath($ownerId, $ownerType), $filters);
    }

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param string|int $id
     * @return ApiResponse
     */
    public function get(string|int $ownerId, string $ownerType, string|int $id): array
    {
        return $this->apiClient->get($this->buildPath($ownerId, $ownerType) . '/' . $id);
    }

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function create(string|int $ownerId, string $ownerType, array $payload): array
    {
        return $this->apiClient->post($this->buildPath($ownerId, $ownerType), $payload);
    }

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param string|int $id
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function update(string|int $ownerId, string $ownerType, string|int $id, array $payload): array
    {
        return $this->apiClient->put($this->buildPath($ownerId, $ownerType) . '/' . $id, $payload);
    }

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param string|int $id
     * @return ApiResponse
     */
    public function delete(string|int $ownerId, string $ownerType, string|int $id): array
    {
        return $this->apiClient->delete($this->buildPath($ownerId, $ownerType) . '/' . $id);
    }
}

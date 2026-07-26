<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class SettingApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/settings';
    }

    /** @return ApiResponse */
    public function getByGroup(string $group): array
    {
        return $this->apiClient->get($this->resourcePath(), ['filter[setting_group]' => $group, 'per_page' => 100]);
    }

    /**
     * @param list<array{id: int, payload: array<string, mixed>}> $updates
     * @return ApiResponse
     */
    public function batchUpdate(array $updates): array
    {
        return $this->apiClient->post($this->resourcePath() . '/batch', ['updates' => $updates]);
    }

    /** @return ApiResponse */
    public function getConnections(int $settingId): array
    {
        return $this->apiClient->get("{$this->resourcePath()}/{$settingId}/connections");
    }

    /**
     * @param array<string, mixed> $data
     * @return ApiResponse
     */
    public function createConnection(int $settingId, array $data): array
    {
        return $this->apiClient->post("{$this->resourcePath()}/{$settingId}/connections", $data);
    }

    /** @return ApiResponse */
    public function deleteConnection(int $settingId, int $connectionId): array
    {
        return $this->apiClient->delete("{$this->resourcePath()}/{$settingId}/connections/{$connectionId}");
    }
}

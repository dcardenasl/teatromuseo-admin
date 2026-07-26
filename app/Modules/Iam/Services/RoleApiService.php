<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class RoleApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/api/v1/iam/roles';
    }

    /** @return ApiResponse */
    public function listPermissions(int|string $id): array
    {
        return $this->apiClient->get($this->resourcePath() . '/' . $id . '/permissions');
    }

    /**
     * @param list<int> $permissionIds
     * @return ApiResponse
     */
    public function attachPermissions(int|string $id, array $permissionIds): array
    {
        return $this->apiClient->post(
            $this->resourcePath() . '/' . $id . '/permissions/attach',
            ['permission_ids' => array_values(array_map(static fn ($v) => (int) $v, $permissionIds))],
        );
    }

    /** @return ApiResponse */
    public function detachPermission(int|string $id, int|string $permissionId): array
    {
        return $this->apiClient->delete($this->resourcePath() . '/' . $id . '/permissions/' . $permissionId);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class AuditApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/audit';
    }

    /** @return ApiResponse */
    public function byEntity(string $type, int|string $id): array
    {
        return $this->apiClient->get('/audit/entity/' . $type . '/' . $id);
    }
}

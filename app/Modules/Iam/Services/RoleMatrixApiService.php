<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Services\BaseApiService;

class RoleMatrixApiService extends BaseApiService
{
    /**
     * @return array<string, mixed>
     */
    public function matrix(): array
    {
        return $this->apiClient->get('/api/v1/iam/role-permission-matrix');
    }
}

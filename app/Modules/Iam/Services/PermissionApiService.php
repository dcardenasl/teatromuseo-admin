<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Services\ResourceApiService;

class PermissionApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/api/v1/iam/permissions';
    }
}

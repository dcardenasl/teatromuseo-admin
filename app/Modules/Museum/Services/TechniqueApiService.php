<?php

declare(strict_types=1);

namespace App\Modules\Museum\Services;

use App\Services\ResourceApiService;

class TechniqueApiService extends ResourceApiService implements TechniqueApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/catalog/techniques';
    }



}

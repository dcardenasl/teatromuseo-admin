<?php

declare(strict_types=1);

namespace App\Modules\Museum\Services;

use App\Services\ResourceApiService;

class CategoryApiService extends ResourceApiService implements CategoryApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/catalog/categories';
    }



}

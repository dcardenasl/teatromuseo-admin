<?php

declare(strict_types=1);

namespace App\Modules\Occurrences\Services;

use App\Services\ResourceApiService;

class OccurrenceApiService extends ResourceApiService implements OccurrenceApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/occurrences';
    }




}

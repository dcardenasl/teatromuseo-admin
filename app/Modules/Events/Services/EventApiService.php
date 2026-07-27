<?php

declare(strict_types=1);

namespace App\Modules\Events\Services;

use App\Services\ResourceApiService;

class EventApiService extends ResourceApiService implements EventApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/events';
    }



}

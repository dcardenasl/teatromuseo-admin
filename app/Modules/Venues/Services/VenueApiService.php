<?php

declare(strict_types=1);

namespace App\Modules\Venues\Services;

use App\Services\ResourceApiService;

class VenueApiService extends ResourceApiService implements VenueApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/venues';
    }



}

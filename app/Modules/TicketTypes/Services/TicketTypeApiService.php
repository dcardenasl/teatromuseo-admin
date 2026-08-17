<?php

declare(strict_types=1);

namespace App\Modules\TicketTypes\Services;

use App\Services\ResourceApiService;

class TicketTypeApiService extends ResourceApiService implements TicketTypeApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/ticket-types';
    }




}

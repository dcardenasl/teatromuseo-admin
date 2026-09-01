<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Services;

use App\Services\ResourceApiService;

class TicketApiService extends ResourceApiService implements TicketApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/events/tickets';
    }




}

<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

class TechniqueUpdateRequest extends TechniqueStoreRequest
{
    public function payload(): array
    {
        $payload = parent::payload();
        unset($payload['sort_order']);

        return $payload;
    }
}

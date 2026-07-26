<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

class EntryUpdateRequest extends EntryStoreRequest
{
    public function payload(): array
    {
        $payload = parent::payload();
        unset($payload['sort_order']);

        return $payload;
    }
}

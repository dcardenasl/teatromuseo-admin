<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

class CategoryUpdateRequest extends CategoryStoreRequest
{
    public function payload(): array
    {
        $payload = parent::payload();
        unset($payload['sort_order']);

        return $payload;
    }
}

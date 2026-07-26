<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

class LanguageUpdateRequest extends LanguageStoreRequest
{
    public function payload(): array
    {
        $payload = parent::payload();
        unset($payload['sort_order']);

        return $payload;
    }
}

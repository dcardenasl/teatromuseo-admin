<?php

declare(strict_types=1);

namespace App\Modules\Events\Requests;

final class EventTypeUpdateRequest extends EventTypeStoreRequest
{
    /** @return array<string, mixed> */
    public function payload(): array
    {
        $payload = parent::payload();
        // The scalar slug is a legacy compatibility projection used by the
        // event FK. Localized slugs may change per language, but the internal
        // projection must remain stable after creation.
        unset($payload['slug']);

        return $payload;
    }
}

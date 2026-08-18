<?php

declare(strict_types=1);

namespace App\Modules\Events\Services;

use App\Libraries\BffApiClientInterface;
use App\Support\BffAvailability;

/** Normalizes the single-read Event workspace for Admin screens. */
final class EventWorkspaceBffAdapter
{
    private bool $lastRequestUnavailable = false;

    public function __construct(private readonly BffApiClientInterface $bffApiClient)
    {
    }

    /** @return array<string,mixed>|null */
    public function workspace(?int $eventId = null): ?array
    {
        $response = $this->bffApiClient->getAdminEventWorkspace($eventId);
        $this->lastRequestUnavailable = BffAvailability::isUnavailable($response);
        if ($this->lastRequestUnavailable) {
            return null;
        }
        if (($response['ok'] ?? false) !== true) {
            return null;
        }
        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

        return is_array($data['sections'] ?? null) ? $data['sections'] : null;
    }

    public function wasUnavailable(): bool
    {
        return $this->lastRequestUnavailable;
    }
}

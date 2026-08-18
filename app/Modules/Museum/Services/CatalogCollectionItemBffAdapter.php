<?php

declare(strict_types=1);

namespace App\Modules\Museum\Services;

use App\Libraries\BffApiClientInterface;

/** Normalizes the single-read Catalog collection-item workspace. */
final class CatalogCollectionItemBffAdapter
{
    private bool $lastRequestUnavailable = false;

    public function __construct(private readonly BffApiClientInterface $bffApiClient)
    {
    }

    /** @return array<string,mixed>|null */
    public function workspace(?int $itemId = null): ?array
    {
        $response = $this->bffApiClient->getAdminCatalogCollectionItemWorkspace($itemId);
        $this->lastRequestUnavailable = ($response['ok'] ?? false) !== true;
        if ($this->lastRequestUnavailable) {
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

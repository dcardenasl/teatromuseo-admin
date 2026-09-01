<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Libraries\BffApiClientInterface;
use App\Support\BffAvailability;

/** Normalizes the single-read CMS category projection for Admin screens. */
final class CmsCategoryBffAdapter
{
    private bool $lastRequestUnavailable = false;

    public function __construct(private readonly BffApiClientInterface $bffApiClient)
    {
    }

    /** @return array<string,mixed>|null */
    public function bootstrap(?int $categoryId = null): ?array
    {
        $response = $this->bffApiClient->getAdminCmsCategoryBootstrap($categoryId);
        $this->lastRequestUnavailable = BffAvailability::isUnavailable($response);
        if ($this->lastRequestUnavailable || ($response['ok'] ?? false) !== true) {
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

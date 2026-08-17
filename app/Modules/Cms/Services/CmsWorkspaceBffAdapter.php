<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Libraries\BffApiClientInterface;

/** Normalizes the single-read CMS workspace projection for Admin views. */
final class CmsWorkspaceBffAdapter
{
    public function __construct(private readonly BffApiClientInterface $bffApiClient)
    {
    }

    /** @return array<string, mixed>|null */
    public function page(int $pageId, ?int $instanceId = null): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsPageWorkspace($pageId, $instanceId));
    }

    /** @return array<string, mixed>|null */
    public function entry(int $entryId, ?int $instanceId = null): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsEntryWorkspace($entryId, $instanceId));
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>|null
     */
    private function sections(array $response): ?array
    {
        if (($response['ok'] ?? false) !== true) {
            return null;
        }

        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $sections = is_array($data['sections'] ?? null) ? $data['sections'] : null;

        return $sections;
    }
}

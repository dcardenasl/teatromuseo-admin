<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Libraries\BffApiClientInterface;

/** Normalizes composite CMS projections for the existing Admin form views. */
final class CmsBootstrapBffAdapter
{
    public function __construct(private readonly BffApiClientInterface $bffApiClient)
    {
    }

    /** @return array<string, mixed>|null */
    public function entryFormOptions(?int $entryId = null): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsEntryFormOptions($entryId));
    }

    /** @return array<string, mixed>|null */
    public function pageFormOptions(?int $pageId = null): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsPageFormOptions($pageId));
    }

    /** @return array<string, mixed>|null */
    public function menuEditorBootstrap(int $menuId, ?int $itemId = null): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsMenuEditorBootstrap($menuId, $itemId));
    }

    /** @return array<string, mixed>|null */
    public function siteIdentityBootstrap(): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsSiteIdentityBootstrap());
    }

    /** @return array<string, mixed>|null */
    public function wizardBootstrap(): ?array
    {
        return $this->sections($this->bffApiClient->getAdminCmsWizardBootstrap());
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

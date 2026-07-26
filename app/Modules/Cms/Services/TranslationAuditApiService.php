<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class TranslationAuditApiService extends BaseApiService
{
    /**
     * @return ApiResponse
     */
    public function getStats(): array
    {
        return $this->apiClient->get('/cms/translations/audit/stats');
    }

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function getReport(array $filters = []): array
    {
        return $this->apiClient->get('/cms/translations/audit/report', $filters);
    }

    /**
     * @param string $type
     * @param int|string $id
     * @return ApiResponse
     */
    public function auditResource(string $type, int|string $id): array
    {
        return $this->apiClient->get('/cms/translations/audit/resource/' . $type . '/' . $id);
    }

    /**
     * Audit every block instance belonging to a single page/entry — powers
     * the contextual translation badges on the owner's "Ver" and "Bloques"
     * views, instead of the sitewide report.
     *
     * @param string $ownerType 'page' | 'entry'
     * @param int|string $ownerId
     * @return ApiResponse
     */
    public function auditOwnerBlocks(string $ownerType, int|string $ownerId): array
    {
        return $this->apiClient->get('/cms/translations/audit/owner/' . $ownerType . '/' . $ownerId);
    }
}

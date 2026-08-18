<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 *
 * Marker interface for the HTTP client that targets a ci4-bff-starter gateway.
 *
 * Functionally identical to {@see ApiClientInterface}; the separate symbol
 * exists so the DI container in {@see \Config\Services} can distinguish the
 * BFF client from the hub and domain clients without ambiguity.
 */
interface BffApiClientInterface extends ApiClientInterface
{
    /** @return ApiResponse */
    public function getAdminDashboard(int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminMetricsWorkspace(string $period = '24h', int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminIamRoleWorkspace(int|string $roleId, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminAnalytics(string $period = '7d', int $maxRetries = 2): array;

    /**
     * @param int|string $fileId
     * @return ApiResponse
     */
    public function getAdminFileUsages(int|string $fileId, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminEventLookups(string $context, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCatalogCollectionItemWorkspace(?int $itemId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminEventWorkspace(?int $eventId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsEntryFormOptions(?int $entryId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsPageFormOptions(?int $pageId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsMenuEditorBootstrap(int $menuId, ?int $itemId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsSiteIdentityBootstrap(int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsPageWorkspace(int|string $pageId, ?int $instanceId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsEntryWorkspace(int|string $entryId, ?int $instanceId = null, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminCmsWizardBootstrap(int $maxRetries = 2): array;
}

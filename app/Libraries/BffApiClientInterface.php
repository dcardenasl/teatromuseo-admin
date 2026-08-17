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
    public function getAdminAnalytics(string $period = '7d', int $maxRetries = 2): array;

    /**
     * @param int|string $fileId
     * @return ApiResponse
     */
    public function getAdminFileUsages(int|string $fileId, int $maxRetries = 2): array;

    /** @return ApiResponse */
    public function getAdminEventLookups(string $context, int $maxRetries = 2): array;
}

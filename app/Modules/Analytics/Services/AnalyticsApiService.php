<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class AnalyticsApiService extends BaseApiService
{
    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function overview(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/overview', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function pages(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/pages', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function referrers(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/referrers', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function devices(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/devices', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function timeseries(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/timeseries', $params);
    }
}

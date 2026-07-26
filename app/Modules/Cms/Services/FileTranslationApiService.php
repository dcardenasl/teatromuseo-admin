<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class FileTranslationApiService extends BaseApiService
{
    /**
     * @return ApiResponse
     */
    public function getForFile(int $fileId): array
    {
        return $this->apiClient->get("/cms/files/{$fileId}/translations");
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function createForFile(int $fileId, array $payload): array
    {
        return $this->apiClient->post("/cms/files/{$fileId}/translations", $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function updateForFile(int $fileId, int $translationId, array $payload): array
    {
        return $this->apiClient->put("/cms/files/{$fileId}/translations/{$translationId}", $payload);
    }
}

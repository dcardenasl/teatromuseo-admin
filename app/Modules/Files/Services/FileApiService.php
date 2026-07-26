<?php

declare(strict_types=1);

namespace App\Modules\Files\Services;

use App\Libraries\ApiClientInterface;
use App\Libraries\DomainApiClientInterface;
use App\Services\ResourceApiService;
use RuntimeException;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class FileApiService extends ResourceApiService
{
    public function __construct(
        ApiClientInterface $apiClient,
        protected DomainApiClientInterface $domainApiClient,
    ) {
        parent::__construct($apiClient);
    }

    protected function resourcePath(): string
    {
        return '/files';
    }

    /**
     * Upload a file to the API using multipart form data.
     *
     * @param array<string, mixed> $fields
     * @return ApiResponse
     */
    public function upload(string $inputName, string $filePath, string $filename, ?string $mimeType = null, array $fields = []): array
    {
        if (! is_file($filePath)) {
            throw new RuntimeException("File does not exist: {$filePath}");
        }

        return $this->apiClient->upload('/files/upload', [
            $inputName => [
                'path'     => $filePath,
                'filename' => $filename,
                'mimeType' => $mimeType,
            ],
        ], $fields);
    }

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function listForPicker(array $filters = []): array
    {
        return $this->apiClient->get('/files', $filters);
    }

    /** @return ApiResponse */
    public function getInfo(int|string $id): array
    {
        return $this->apiClient->get('/files/' . $id . '/info');
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function updateMetadata(int|string $id, array $payload): array
    {
        return $this->apiClient->patch('/files/' . $id, $payload);
    }

    /** @return ApiResponse */
    public function restore(int|string $id): array
    {
        return $this->apiClient->post('/files/' . $id . '/restore');
    }

    /** @return ApiResponse */
    public function forceDelete(int|string $id): array
    {
        return $this->apiClient->delete('/files/' . $id . '/force');
    }

    /** @return ApiResponse */
    public function usages(int|string $id): array
    {
        $hubResponse    = $this->apiClient->get('/files/' . $id . '/usages');
        $domainResponse = $this->domainApiClient->get('/cms/files/' . $id . '/usages');

        $hubItems    = is_array($hubResponse['data'] ?? null) ? (array) $hubResponse['data'] : [];
        $domainItems = is_array($domainResponse['data'] ?? null) ? (array) $domainResponse['data'] : [];

        /** @var array<string, mixed> $merged */
        $merged = array_merge($hubItems, $domainItems);

        return array_merge($hubResponse, ['data' => $merged]);
    }


    /** @return ApiResponse */
    public function regenerateVariants(int|string $id): array
    {
        return $this->apiClient->post('/files/' . $id . '/regenerate-variants');
    }

    /**
     * @param list<int|string> $ids
     * @return ApiResponse
     */
    public function bulkDelete(array $ids): array
    {
        return $this->apiClient->post('/files/bulk-delete', ['ids' => $this->stringifyIds($ids)]);
    }

    /**
     * @param list<int|string> $ids
     * @return ApiResponse
     */
    public function bulkRestore(array $ids): array
    {
        return $this->apiClient->post('/files/bulk-restore', ['ids' => $this->stringifyIds($ids)]);
    }

    /**
     * @param list<int|string> $ids
     * @return ApiResponse
     */
    public function bulkForceDelete(array $ids): array
    {
        return $this->apiClient->post('/files/bulk-force-delete', ['ids' => $this->stringifyIds($ids)]);
    }

    /**
     * Serialize ids as strings to dodge CI4's `InvalidChars` global filter,
     * which throws `TypeError` from `mb_check_encoding()` when it recurses
     * into a JSON body containing raw integers. The API DTO casts back.
     *
     * @param list<int|string> $ids
     * @return list<string>
     */
    private function stringifyIds(array $ids): array
    {
        return array_values(array_map(static fn ($id): string => (string) $id, $ids));
    }
}

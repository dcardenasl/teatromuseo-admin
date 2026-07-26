<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class BlockTypeApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/block-types';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function templates(): array
    {
        $response = $this->apiClient->get('/cms/block-types/templates');
        if (! isset($response['ok']) || ! $response['ok']) {
            return [];
        }

        // ApiClient puts the full payload in ['data'], so actual items are at ['data']['data']
        $payload = $response['data'] ?? [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }
        return is_array($payload) ? array_values($payload) : [];
    }

    /** @return ApiResponse */
    public function usages(int|string $id): array
    {
        return $this->apiClient->get('/cms/block-types/' . $id . '/usages');
    }
}

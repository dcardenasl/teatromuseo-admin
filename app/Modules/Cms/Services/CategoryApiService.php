<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class CategoryApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/categories';
    }




    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     * @param array<string, mixed> $filters
     */
    public function collections(array $filters = []): array
    {
        return $this->apiClient->get('/cms/collections', $filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     * @param array<string, mixed> $filters
     */
    public function categories(array $filters = []): array
    {
        return $this->apiClient->get('/cms/categories', $filters);
    }

    /** @return ApiResponse */
    public function checkSlug(string $slug, int $languageId, string $currentId = ''): array
    {
        $params = ['slug' => $slug, 'language_id' => $languageId];
        if ($currentId !== '') {
            $params['current_id'] = $currentId;
        }
        return $this->apiClient->get('/cms/categories/check-slug', $params);
    }
}

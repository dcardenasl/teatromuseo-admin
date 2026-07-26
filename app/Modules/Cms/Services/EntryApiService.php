<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class EntryApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/entries';
    }


    /** @return ApiResponse */
    public function publish(int|string $id): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/publish');
    }

    /** @return ApiResponse */
    public function archive(int|string $id): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/archive');
    }

    /**
     * Replace the categories assigned to an entry.
     *
     * @param list<int> $categoryIds
     * @return ApiResponse
     */
    public function syncCategories(int|string $id, array $categoryIds): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/categories', [
            'category_ids' => $categoryIds,
        ]);
    }

    /**
     * Replace the tags assigned to an entry.
     *
     * @param list<int> $tagIds
     * @return ApiResponse
     */
    public function syncTags(int|string $id, array $tagIds): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/tags', [
            'tag_ids' => $tagIds,
        ]);
    }

    /**
     * Replace all taxonomy relations in a single atomic request.
     *
     * @param list<int> $categoryIds
     * @param list<int> $tagIds
     * @return ApiResponse
     */
    public function syncTaxonomy(int|string $id, array $categoryIds, array $tagIds): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/taxonomy', [
            'category_ids' => $categoryIds,
            'tag_ids' => $tagIds,
        ]);
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

    /** @return ApiResponse */
    public function checkSlug(string $slug, int $languageId, string $currentId = ''): array
    {
        $params = ['slug' => $slug, 'language_id' => $languageId];
        if ($currentId !== '') {
            $params['current_id'] = $currentId;
        }
        return $this->apiClient->get('/cms/entries/check-slug', $params);
    }
}

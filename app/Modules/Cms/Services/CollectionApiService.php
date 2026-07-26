<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class CollectionApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/collections';
    }

    /** @return ApiResponse */
    public function checkSlug(string $slug, int $languageId, string $currentId = ''): array
    {
        $params = ['slug' => $slug, 'language_id' => $languageId];

        if ($currentId !== '') {
            $params['current_id'] = $currentId;
        }

        return $this->apiClient->get('/cms/collections/check-slug', $params);
    }
}

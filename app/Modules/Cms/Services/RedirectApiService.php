<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class RedirectApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/redirects';
    }



    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function exportCsv(array $filters = []): array
    {
        return $this->list($filters);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return ApiResponse
     */
    public function importCsv(array $rows): array
    {
        $created = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $created[] = $this->create($row);
        }

        return [
            'ok'          => true,
            'status'      => 200,
            'data'        => ['items' => $created],
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }

}

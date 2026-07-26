<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class FormSubmissionApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/submissions';
    }

    /**
     * @return array<string, mixed>
     */
    public function counts(): array
    {
        return $this->apiClient->get($this->resourcePath() . '/counts');
    }

    /**
     * @return array<string, mixed>
     */
    public function updateStatus(int|string $id, string $status): array
    {
        return $this->apiClient->patch($this->resourcePath() . '/' . $id . '/status', ['status' => $status]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class FormApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/forms';
    }

    /**
     * @return array<string, mixed>
     */
    public function getFields(int|string $formId): array
    {
        return $this->apiClient->get($this->resourcePath() . '/' . $formId . '/fields');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @param array<string, mixed> $data
     */
    public function createField(int|string $formId, array $data): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $formId . '/fields', $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @param array<string, mixed> $data
     */
    public function updateField(int|string $formId, int|string $fieldId, array $data): array
    {
        return $this->apiClient->put($this->resourcePath() . '/' . $formId . '/fields/' . $fieldId, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteField(int|string $formId, int|string $fieldId): array
    {
        return $this->apiClient->delete($this->resourcePath() . '/' . $formId . '/fields/' . $fieldId);
    }

    /**
     * @param list<int> $orderedIds
     * @return array<string, mixed>
     */
    public function reorderFields(int|string $formId, array $orderedIds): array
    {
        return $this->apiClient->patch($this->resourcePath() . '/' . $formId . '/fields/reorder', ['ordered_ids' => $orderedIds]);
    }
}

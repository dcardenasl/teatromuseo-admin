<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Modules\Iam\Support\IamLookups;
use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class UserApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/users';
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function create(array $payload): array
    {
        $response = parent::create($payload);
        $this->invalidateLookupsOnSuccess($response);

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function update(int|string $id, array $payload): array
    {
        $response = parent::update($id, $payload);
        $this->invalidateLookupsOnSuccess($response);

        return $response;
    }

    /** @return ApiResponse */
    public function delete(int|string $id): array
    {
        $response = parent::delete($id);
        $this->invalidateLookupsOnSuccess($response);

        return $response;
    }

    /** @return ApiResponse */
    public function approve(int|string $id, ?string $locale = null): array
    {
        $payload = [];
        if ($locale !== null && $locale !== '') {
            $payload['locale'] = $locale;
        }

        $response = $this->apiClient->post('/users/' . $id . '/approve', $payload);
        $this->invalidateLookupsOnSuccess($response);

        return $response;
    }

    /** @return ApiResponse */
    public function assignableRoles(): array
    {
        return $this->apiClient->get('/users/assignable-roles');
    }

    /**
     * @param array<string, mixed> $response
     */
    private function invalidateLookupsOnSuccess(array $response): void
    {
        $status = (int) ($response['status'] ?? 0);
        if ($status >= 200 && $status < 300) {
            IamLookups::invalidateUsers();
        }
    }
}

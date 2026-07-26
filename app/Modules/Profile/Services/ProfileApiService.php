<?php

declare(strict_types=1);

namespace App\Modules\Profile\Services;

use App\Services\BaseApiService;

/**
 * Profile API Service
 *
 * Wraps API endpoints for user profile operations.
 * Consolidates authentication and user data endpoints used by ProfileController.
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class ProfileApiService extends BaseApiService
{
    /**
     * Get authenticated user profile
     *
     * @return ApiResponse
     */
    public function me(): array
    {
        return $this->apiClient->get('/auth/me');
    }

    /**
     * Update the authenticated user's own profile.
     *
     * Targets the dedicated self-update endpoint `/auth/me` so the API can
     * enforce the self-only allowlist (first_name, last_name, avatar_url) and
     * keep the admin endpoint blocked for self-edit. The $userId argument is
     * accepted for existing callers but is intentionally not used — the API
     * derives the subject from the JWT.
     *
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function update(string $userId, array $payload): array
    {
        return $this->apiClient->patch('/auth/me', $payload);
    }

    /**
     * Request password reset email
     *
     * @return ApiResponse
     */
    public function forgotPassword(string $email, string $clientBaseUrl, ?string $locale = null): array
    {
        return $this->apiClient->publicPost('/auth/forgot-password', [
            'email'            => $email,
            'client_base_url'  => $clientBaseUrl,
            'locale'           => $locale ?? '',
        ]);
    }

    /**
     * Resend email verification
     *
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function resendVerification(array $payload = []): array
    {
        return $this->apiClient->post('/auth/resend-verification', $payload);
    }
}

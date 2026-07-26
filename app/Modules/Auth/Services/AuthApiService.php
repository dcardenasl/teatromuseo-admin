<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class AuthApiService extends BaseApiService
{
    /**
     * @param array<string, mixed> $credentials
     * @return ApiResponse
     */
    public function login(array $credentials): array
    {
        return $this->apiClient->publicPost('/auth/login', $credentials);
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function googleLogin(array $payload): array
    {
        return $this->apiClient->publicPost('/auth/google-login', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function register(array $payload): array
    {
        return $this->apiClient->publicPost('/auth/register', $payload);
    }

    /** @return ApiResponse */
    public function forgotPassword(string $email, ?string $clientBaseUrl = null, ?string $locale = null): array
    {
        $payload = ['email' => $email];
        if ($clientBaseUrl !== null && $clientBaseUrl !== '') {
            $payload['client_base_url'] = $clientBaseUrl;
        }
        if ($locale !== null && $locale !== '') {
            $payload['locale'] = $locale;
        }

        return $this->apiClient->publicPost('/auth/forgot-password', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function resetPassword(array $payload): array
    {
        return $this->apiClient->publicPost('/auth/reset-password', $payload);
    }

    /** @return ApiResponse */
    public function verifyEmail(string $token): array
    {
        return $this->apiClient->publicGet('/auth/verify-email', ['token' => $token]);
    }

    /** @return ApiResponse */
    public function logout(): array
    {
        return $this->apiClient->post('/auth/revoke');
    }

    /** @return ApiResponse */
    public function me(): array
    {
        return $this->apiClient->get('/auth/me');
    }

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function resendVerification(array $payload = []): array
    {
        return $this->apiClient->post('/auth/resend-verification', $payload);
    }
}

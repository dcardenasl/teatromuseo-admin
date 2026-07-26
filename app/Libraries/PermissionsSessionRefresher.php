<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Modules\Auth\Services\AuthApiService;
use App\Support\SessionKeys;

final class PermissionsSessionRefresher
{
    private const SESSION_REFRESHED_AT = 'permissions_refreshed_at';

    public function __construct(private readonly AuthApiService $authService)
    {
    }

    public function refreshIfStale(int $ttlSeconds = 60): void
    {
        $lastRefresh = session(self::SESSION_REFRESHED_AT);
        if (is_int($lastRefresh) && $lastRefresh > time() - $ttlSeconds) {
            return;
        }

        $this->forceRefresh();
    }

    public function forceRefresh(): void
    {
        try {
            $response = $this->authService->me();
        } catch (\Throwable $exception) {
            log_message('warning', 'Permission refresh skipped because Hub is unavailable: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        if (! ($response['ok'] ?? false)) {
            return;
        }

        $data = $response['data']['data'] ?? $response['data'] ?? [];
        if (! is_array($data) || $data === []) {
            return;
        }

        session()->set(SessionKeys::USER->value, $data);
        session()->set(self::SESSION_REFRESHED_AT, time());
    }
}

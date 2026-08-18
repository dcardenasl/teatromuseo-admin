<?php

declare(strict_types=1);

namespace App\Support;

/** Classifies transport and server failures separately from client errors. */
final class BffAvailability
{
    /**
     * A missing status or server error means the BFF was unavailable. Client,
     * authentication and authorization responses remain caller-visible.
     *
     * @param array<string, mixed> $response
     */
    public static function isUnavailable(array $response): bool
    {
        $status = (int) ($response['status'] ?? 0);

        return $status === 0 || $status >= 500;
    }
}

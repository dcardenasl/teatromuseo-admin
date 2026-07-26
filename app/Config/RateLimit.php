<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Request-throttle limits for RateLimitFilter.
 *
 * Centralizing these here (instead of reading env() directly in the filter)
 * keeps env access confined to Config classes, matching AdminAccess.php and
 * the rest of the repo's convention.
 */
class RateLimit extends BaseConfig
{
    /** Max requests allowed per window. Override via env var `ADMIN_RATE_LIMIT_REQUESTS`. */
    public int $maxRequests = 200;

    /** Window size in seconds. Override via env var `ADMIN_RATE_LIMIT_WINDOW`. */
    public int $windowSeconds = 60;

    public function __construct()
    {
        parent::__construct();

        $maxRequests = env('ADMIN_RATE_LIMIT_REQUESTS');
        if ($maxRequests !== null && $maxRequests !== '') {
            $this->maxRequests = max(1, (int) $maxRequests);
        }

        $windowSeconds = env('ADMIN_RATE_LIMIT_WINDOW');
        if ($windowSeconds !== null && $windowSeconds !== '') {
            $this->windowSeconds = max(1, (int) $windowSeconds);
        }
    }
}

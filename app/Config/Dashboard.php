<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class Dashboard extends BaseConfig
{
    public int $freshTtl = 300;
    public int $staleTtl = 3600;
    public int $failureCooldownTtl = 15;
    public int $lockMaxAge = 120;
    public int $lockWaitMs = 250;
    public int $upstreamMaxRetries = 0;

    public function __construct()
    {
        parent::__construct();

        $this->freshTtl = $this->positiveEnv('ADMIN_DASHBOARD_FRESH_TTL', $this->freshTtl);
        $this->staleTtl = max($this->freshTtl, $this->positiveEnv('ADMIN_DASHBOARD_STALE_TTL', $this->staleTtl));
        $this->failureCooldownTtl = max(0, $this->integerEnv('ADMIN_DASHBOARD_FAILURE_COOLDOWN', $this->failureCooldownTtl));
        $this->lockMaxAge = $this->positiveEnv('ADMIN_DASHBOARD_LOCK_MAX_AGE', $this->lockMaxAge);
        $this->lockWaitMs = max(0, $this->integerEnv('ADMIN_DASHBOARD_LOCK_WAIT_MS', $this->lockWaitMs));
        $this->upstreamMaxRetries = max(0, min(1, $this->integerEnv('ADMIN_DASHBOARD_MAX_RETRIES', $this->upstreamMaxRetries)));
    }

    private function positiveEnv(string $key, int $default): int
    {
        return max(1, $this->integerEnv($key, $default));
    }

    private function integerEnv(string $key, int $default): int
    {
        $value = env($key);

        return is_numeric($value) ? (int) $value : $default;
    }
}

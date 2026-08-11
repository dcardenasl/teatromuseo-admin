<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

interface DashboardLockInterface
{
    public function acquire(string $key): ?string;

    public function release(string $key, string $token): void;
}

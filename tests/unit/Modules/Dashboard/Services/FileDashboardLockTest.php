<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dashboard\Services;

use App\Modules\Dashboard\Services\FileDashboardLock;
use PHPUnit\Framework\TestCase;

final class FileDashboardLockTest extends TestCase
{
    public function testBusyLockFailsImmediatelyWhenConfiguredNonBlocking(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'teatromuseo-dashboard-lock-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0750, true));

        $owner = new FileDashboardLock($directory, 120, 0);
        $token = $owner->acquire('dashboard-key');
        self::assertIsString($token);

        try {
            $contender = new FileDashboardLock($directory, 120, 0);

            self::assertNull($contender->acquire('dashboard-key'));
        } finally {
            $owner->release('dashboard-key', $token);
            rmdir($directory);
        }
    }
}

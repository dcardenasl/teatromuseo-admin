<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\RateLimit;

/**
 * @internal
 */
final class RateLimitConfigTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        putenv('ADMIN_RATE_LIMIT_REQUESTS');
        putenv('ADMIN_RATE_LIMIT_WINDOW');
        unset($_ENV['ADMIN_RATE_LIMIT_REQUESTS'], $_ENV['ADMIN_RATE_LIMIT_WINDOW']);
        parent::tearDown();
    }

    public function testDefaultsMatchThePreviousFilterConstants(): void
    {
        $config = new RateLimit();

        $this->assertSame(200, $config->maxRequests);
        $this->assertSame(60, $config->windowSeconds);
    }

    public function testEnvVarsOverrideDefaults(): void
    {
        putenv('ADMIN_RATE_LIMIT_REQUESTS=50');
        putenv('ADMIN_RATE_LIMIT_WINDOW=30');

        $config = new RateLimit();

        $this->assertSame(50, $config->maxRequests);
        $this->assertSame(30, $config->windowSeconds);
    }

    public function testEnvVarsBelowOneAreClampedToOne(): void
    {
        putenv('ADMIN_RATE_LIMIT_REQUESTS=0');
        putenv('ADMIN_RATE_LIMIT_WINDOW=-5');

        $config = new RateLimit();

        $this->assertSame(1, $config->maxRequests);
        $this->assertSame(1, $config->windowSeconds);
    }
}

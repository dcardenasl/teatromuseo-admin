<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Tests\Support\Fixtures\AdminFixtureFactory;

/** @internal */
final class AdminFixtureFactoryTest extends TestCase
{
    public function testLanguageFixturesScaleWithoutChangingTheFactory(): void
    {
        $factory = new AdminFixtureFactory('fixture-scope');
        $languages = $factory->languages(4);

        $this->assertCount(4, $languages);
        $this->assertCount(4, array_unique(array_column($languages, 'id')));
        $this->assertCount(4, array_unique(array_column($languages, 'code')));
        $this->assertSame(1, $languages[1]['id'] - $languages[0]['id']);
        $this->assertTrue($languages[0]['is_default']);
        $this->assertFalse($languages[1]['is_default']);
    }
}

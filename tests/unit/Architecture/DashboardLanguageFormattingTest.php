<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

final class DashboardLanguageFormattingTest extends CIUnitTestCase
{
    public function testNamedUnavailableSourceMessageUsesCodeIgniterPlaceholderContract(): void
    {
        foreach (['es', 'en'] as $locale) {
            $catalog = require APPPATH . "Language/{$locale}/Dashboard.php";
            $message = (string) $catalog['source_unavailable_named'];

            self::assertStringContainsString('{0}', $message, "Dashboard source warning must expose {0} ({$locale}).");
            self::assertStringNotContainsString('%s', $message, "Dashboard source warning must not expose printf placeholders ({$locale}).");
        }
    }
}

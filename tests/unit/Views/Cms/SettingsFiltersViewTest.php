<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SettingsFiltersViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testSettingsFiltersDoNotAdvertiseDeadAutoApplyContract(): void
    {
        $html = view('cms/settings/partials/filters', [
            'limitOptions' => [10, 25, 50, 100],
        ]);

        $this->assertStringContainsString('name="setting_group"', $html);
        foreach (['identity', 'contact', 'integration', 'analytics', 'social'] as $group) {
            $this->assertStringContainsString('value="' . $group . '"', $html);
        }
        foreach (['general', 'seo', 'cms_meta'] as $obsoleteGroup) {
            $this->assertStringNotContainsString('value="' . $obsoleteGroup . '"', $html);
        }
        $this->assertStringNotContainsString('data-table-filter', $html);
    }
}

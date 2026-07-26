<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SettingCreateViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testCreateViewKeepsOldSettingTypeAfterValidationFailure(): void
    {
        service('session')->set('_ci_old_input', [
            'post' => [
                'setting_type' => 'int',
            ],
        ]);

        $html = view('cms/settings/create', [
            'title' => 'New Setting',
            'item'  => [],
            'errors' => [],
        ]);

        $this->assertStringContainsString("settingType: 'int'", $html);
        $this->assertStringContainsString('isTranslatable:', $html);
    }
}

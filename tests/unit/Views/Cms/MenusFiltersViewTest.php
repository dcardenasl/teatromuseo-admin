<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class MenusFiltersViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testMenusFiltersDoNotAdvertiseDeadAutoApplyContract(): void
    {
        $html = view('cms/menus/partials/filters', [
            'limitOptions' => [10, 25, 50, 100],
        ]);

        $this->assertStringContainsString('name="is_active"', $html);
        $this->assertStringNotContainsString('data-table-filter', $html);
    }
}
